<?php
// ============================================
// create_product.php - Създаване на нов продукт
// POST: api/products/create_product.php
// САМО ЗА ADMIN!
// ============================================

require_once '../config.php';

// Приемаме само POST заявки
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, [
        'success' => false,
        'message' => 'Позволен е само POST метод'
    ]);
}

// Проверка дали user-ът е logged in и е admin
if (!isLoggedIn()) {
    sendResponse(401, [
        'success' => false,
        'message' => 'Трябва да сте влезли в системата'
    ]);
}

if (!isAdmin()) {
    sendResponse(403, [
        'success' => false,
        'message' => 'Нямате права за добавяне на продукти'
    ]);
}

// Вземаме JSON данните от request-а
$data = getJSONInput();

// ============================================
// ВАЛИДАЦИЯ НА ВХОДНИТЕ ДАННИ
// ============================================

// Проверяваме задължителните полета
$required_fields = ['brand_id', 'model_name', 'price', 'btu_power', 'energy_class'];
$errors = validateRequired($data, $required_fields);

if (!empty($errors)) {
    sendResponse(400, [
        'success' => false,
        'message' => 'Моля попълнете всички задължителни полета',
        'errors' => $errors
    ]);
}

// Sanitize входните данни
$brand_id = intval($data['brand_id']);
$model_name = sanitizeInput($data['model_name']);
$description = isset($data['description']) ? sanitizeInput($data['description']) : '';
$price = floatval($data['price']);
$stock_quantity = isset($data['stock_quantity']) ? intval($data['stock_quantity']) : 0;
$energy_class = sanitizeInput($data['energy_class']);
$btu_power = intval($data['btu_power']);
$warranty_months = isset($data['warranty_months']) ? intval($data['warranty_months']) : 24;
$main_image_url = isset($data['main_image_url']) ? sanitizeInput($data['main_image_url']) : '';
$features = isset($data['features']) ? sanitizeInput($data['features']) : '';
$weight_kg = isset($data['weight_kg']) ? floatval($data['weight_kg']) : 0;
$dimensions = isset($data['dimensions']) ? sanitizeInput($data['dimensions']) : '';
$is_active = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1;

// Валидации
if ($price <= 0) {
    sendResponse(400, [
        'success' => false,
        'message' => 'Цената трябва да е положително число'
    ]);
}

if ($btu_power <= 0) {
    sendResponse(400, [
        'success' => false,
        'message' => 'BTU мощността трябва да е положително число'
    ]);
}

// Проверка дали марката съществува
try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT brand_id FROM brands WHERE brand_id = :brand_id");
    $stmt->bindParam(':brand_id', $brand_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        sendResponse(400, [
            'success' => false,
            'message' => 'Избраната марка не съществува'
        ]);
    }
    
} catch(PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при проверка на марката',
        'error' => DEBUG_MODE ? $e->getMessage() : 'Моля опитайте отново'
    ]);
}

// ============================================
// СЪЗДАВАНЕ НА ПРОДУКТА
// ============================================

try {
    $conn = getDBConnection();
    
    // Вмъкваме новия продукт
    $stmt = $conn->prepare("
        INSERT INTO products (
            brand_id, model_name, description, price, stock_quantity, 
            energy_class, btu_power, warranty_months, main_image_url, 
            features, weight_kg, dimensions, is_active
        ) VALUES (
            :brand_id, :model_name, :description, :price, :stock_quantity,
            :energy_class, :btu_power, :warranty_months, :main_image_url,
            :features, :weight_kg, :dimensions, :is_active
        )
    ");
    
    $stmt->bindParam(':brand_id', $brand_id);
    $stmt->bindParam(':model_name', $model_name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':stock_quantity', $stock_quantity);
    $stmt->bindParam(':energy_class', $energy_class);
    $stmt->bindParam(':btu_power', $btu_power);
    $stmt->bindParam(':warranty_months', $warranty_months);
    $stmt->bindParam(':main_image_url', $main_image_url);
    $stmt->bindParam(':features', $features);
    $stmt->bindParam(':weight_kg', $weight_kg);
    $stmt->bindParam(':dimensions', $dimensions);
    $stmt->bindParam(':is_active', $is_active);
    
    $stmt->execute();
    
    // Вземаме ID на новосъздадения продукт
    $product_id = $conn->lastInsertId();
    
    // Вземаме пълната информация за продукта
    $stmt = $conn->prepare("
        SELECT p.*, b.brand_name 
        FROM products p 
        JOIN brands b ON p.brand_id = b.brand_id 
        WHERE p.product_id = :product_id
    ");
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $product = $stmt->fetch();
    
    // Връщаме успешен отговор
    sendResponse(201, [
        'success' => true,
        'message' => 'Продуктът беше добавен успешно!',
        'product' => $product
    ]);
    
} catch(PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при създаване на продукта',
        'error' => DEBUG_MODE ? $e->getMessage() : 'Моля опитайте отново'
    ]);
}
?>