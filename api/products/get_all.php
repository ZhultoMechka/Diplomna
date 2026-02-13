<?php
// ============================================
// get_all.php - Вземане на всички продукти
// GET: api/products/get_all.php
// ============================================

require_once '../config.php';

// Приемаме само GET заявки
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(405, [
        'success' => false,
        'message' => 'Позволен е само GET метод'
    ]);
}

// ============================================
// ФИЛТРИРАНЕ И ТЪРСЕНЕ
// ============================================

// Параметри за филтриране (от URL query string)
$brand_id = isset($_GET['brand_id']) ? intval($_GET['brand_id']) : null;
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
$btu_power = isset($_GET['btu_power']) ? intval($_GET['btu_power']) : null;
$energy_class = isset($_GET['energy_class']) ? sanitizeInput($_GET['energy_class']) : null;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : null;
$is_active = isset($_GET['is_active']) ? intval($_GET['is_active']) : null;

// Сортиране
$sort_by = isset($_GET['sort_by']) ? sanitizeInput($_GET['sort_by']) : 'product_id';
$sort_order = isset($_GET['sort_order']) && strtoupper($_GET['sort_order']) === 'DESC' ? 'DESC' : 'ASC';

// Валидни полета за сортиране
$valid_sort_fields = ['product_id', 'model_name', 'price', 'btu_power', 'stock_quantity', 'created_at'];
if (!in_array($sort_by, $valid_sort_fields)) {
    $sort_by = 'product_id';
}

// ============================================
// ВЗЕМАНЕ НА ПРОДУКТИТЕ ОТ БАЗАТА
// ============================================

try {
    $conn = getDBConnection();
    
    // Започваме със базовата заявка
    $sql = "
        SELECT 
            p.*,
            b.brand_name,
            b.logo_url as brand_logo
        FROM products p
        JOIN brands b ON p.brand_id = b.brand_id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Добавяме филтри ако има
    if ($brand_id !== null) {
        $sql .= " AND p.brand_id = :brand_id";
        $params[':brand_id'] = $brand_id;
    }
    
    if ($min_price !== null) {
        $sql .= " AND p.price >= :min_price";
        $params[':min_price'] = $min_price;
    }
    
    if ($max_price !== null) {
        $sql .= " AND p.price <= :max_price";
        $params[':max_price'] = $max_price;
    }
    
    if ($btu_power !== null) {
        $sql .= " AND p.btu_power = :btu_power";
        $params[':btu_power'] = $btu_power;
    }
    
    if ($energy_class !== null) {
        $sql .= " AND p.energy_class = :energy_class";
        $params[':energy_class'] = $energy_class;
    }
    
    if ($is_active !== null) {
        $sql .= " AND p.is_active = :is_active";
        $params[':is_active'] = $is_active;
    }
    
    // Търсене в модел и описание
    if ($search !== null && $search !== '') {
        $sql .= " AND (p.model_name LIKE :search OR p.description LIKE :search OR b.brand_name LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    // Добавяме сортиране
    $sql .= " ORDER BY p.$sort_by $sort_order";
    
    // Изпълняваме заявката
    $stmt = $conn->prepare($sql);
    
    // Bind параметрите
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    // Вземаме общия брой продукти (без филтри) за статистика
    $stmt_total = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
    $stmt_total->execute();
    $total_active = $stmt_total->fetch()['total'];
    
    // Форматираме отговора
    sendResponse(200, [
        'success' => true,
        'count' => count($products),
        'total_active_products' => $total_active,
        'filters_applied' => [
            'brand_id' => $brand_id,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'btu_power' => $btu_power,
            'energy_class' => $energy_class,
            'search' => $search,
            'is_active' => $is_active
        ],
        'products' => $products
    ]);
    
} catch(PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при вземане на продуктите',
        'error' => DEBUG_MODE ? $e->getMessage() : 'Моля опитайте отново'
    ]);
}
?>