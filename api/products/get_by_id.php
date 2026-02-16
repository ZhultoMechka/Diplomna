<?php
// ============================================
// get_by_id.php - Вземане на един продукт по ID
// GET: api/products/get_by_id.php?id=5
// ============================================

require_once '../config.php';

// Приемаме само GET заявки
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(405, [
        'success' => false,
        'message' => 'Позволен е само GET метод'
    ]);
}

// Проверка за ID параметър
if (!isset($_GET['id']) || empty($_GET['id'])) {
    sendResponse(400, [
        'success' => false,
        'message' => 'Липсва ID на продукт'
    ]);
}

$product_id = intval($_GET['id']);

if ($product_id <= 0) {
    sendResponse(400, [
        'success' => false,
        'message' => 'Невалиден ID на продукт'
    ]);
}

// ============================================
// ВЗЕМАНЕ НА ПРОДУКТА ОТ БАЗАТА
// ============================================

try {
    $conn = getDBConnection();
    
    // Вземаме детайлите на продукта
    $sql = "
        SELECT 
            p.*,
            b.brand_name,
            b.logo_url as brand_logo
        FROM products p
        JOIN brands b ON p.brand_id = b.brand_id
        WHERE p.product_id = :product_id
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $product = $stmt->fetch();
    
    if (!$product) {
        sendResponse(404, [
            'success' => false,
            'message' => 'Продуктът не е намерен'
        ]);
    }
    
    // Вземаме допълнителни снимки на продукта (ако има)
    $sql_images = "
        SELECT image_url, is_primary
        FROM product_images
        WHERE product_id = :product_id
        ORDER BY is_primary DESC
    ";
    
    $stmt_images = $conn->prepare($sql_images);
    $stmt_images->bindValue(':product_id', $product_id, PDO::PARAM_INT);
    $stmt_images->execute();
    
    $images = $stmt_images->fetchAll();
    
    // Ако няма снимки в product_images таблицата, използваме main_image_url
    if (empty($images) && !empty($product['main_image_url'])) {
        $images = [
            [
                'image_url' => $product['main_image_url'],
                'is_primary' => 1
            ]
        ];
    }
    
    // Добавяме снимките към продукта
    $product['images'] = $images;
    
    // Вземаме свързани продукти (същата марка, същ BTU диапазон)
    $sql_related = "
        SELECT 
            p.product_id,
            p.model_name,
            p.price,
            p.main_image_url,
            p.energy_class,
            b.brand_name
        FROM products p
        JOIN brands b ON p.brand_id = b.brand_id
        WHERE p.product_id != :product_id
        AND p.is_active = 1
        AND (
            p.brand_id = :brand_id 
            OR ABS(p.btu_power - :btu_power) <= 3000
        )
        ORDER BY 
            CASE WHEN p.brand_id = :brand_id THEN 0 ELSE 1 END,
            ABS(p.btu_power - :btu_power)
        LIMIT 4
    ";
    
    $stmt_related = $conn->prepare($sql_related);
    $stmt_related->bindValue(':product_id', $product_id, PDO::PARAM_INT);
    $stmt_related->bindValue(':brand_id', $product['brand_id'], PDO::PARAM_INT);
    $stmt_related->bindValue(':btu_power', $product['btu_power'], PDO::PARAM_INT);
    $stmt_related->execute();
    
    $related_products = $stmt_related->fetchAll();
    
    // Добавяме свързаните продукти
    $product['related_products'] = $related_products;
    
    // Форматираме отговора
    sendResponse(200, [
        'success' => true,
        'product' => $product
    ]);
    
} catch(PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при вземане на продукта',
        'error' => DEBUG_MODE ? $e->getMessage() : 'Моля опитайте отново'
    ]);
}
?>