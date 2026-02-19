<?php
// ============================================
// update.php - Обновяване на продукт
// PUT/POST: api/products/update.php
// ============================================

require_once '../config.php';

// Приемаме POST или PUT
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    sendResponse(405, ['success' => false, 'message' => 'Позволени са само POST/PUT методи']);
}

// Проверка дали е admin (по-късно)
// requireLogin();
// if ($_SESSION['user_type'] !== 'admin') {
//     sendResponse(403, ['success' => false, 'message' => 'Нямате права за това действие']);
// }

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    sendResponse(400, ['success' => false, 'message' => 'Невалидни данни']);
}

// Задължителни полета
$required = ['product_id', 'brand_id', 'model_name', 'price', 'btu_power', 'energy_class', 'warranty_months', 'stock_quantity'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        sendResponse(400, ['success' => false, 'message' => "Липсва поле: $field"]);
    }
}

try {
    $conn = getDBConnection();

    // Проверка дали продуктът съществува
    $check = $conn->prepare("SELECT product_id FROM products WHERE product_id = :id");
    $check->execute([':id' => $data['product_id']]);
    
    if (!$check->fetch()) {
        sendResponse(404, ['success' => false, 'message' => 'Продуктът не съществува']);
    }

    // Обновяване на продукта
    $sql = "
        UPDATE products SET
            brand_id = :brand_id,
            model_name = :model_name,
            description = :description,
            price = :price,
            stock_quantity = :stock_quantity,
            energy_class = :energy_class,
            btu_power = :btu_power,
            warranty_months = :warranty_months,
            main_image_url = :main_image_url,
            features = :features,
            weight_kg = :weight_kg,
            dimensions = :dimensions,
            is_active = :is_active,
            updated_at = NOW()
        WHERE product_id = :product_id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':product_id'      => $data['product_id'],
        ':brand_id'        => $data['brand_id'],
        ':model_name'      => $data['model_name'],
        ':description'     => $data['description'] ?? null,
        ':price'           => $data['price'],
        ':stock_quantity'  => $data['stock_quantity'],
        ':energy_class'    => $data['energy_class'],
        ':btu_power'       => $data['btu_power'],
        ':warranty_months' => $data['warranty_months'],
        ':main_image_url'  => $data['main_image_url'] ?? null,
        ':features'        => $data['features'] ?? null,
        ':weight_kg'       => $data['weight_kg'] ?? null,
        ':dimensions'      => $data['dimensions'] ?? null,
        ':is_active'       => isset($data['is_active']) ? $data['is_active'] : 1
    ]);

    sendResponse(200, [
        'success' => true,
        'message' => 'Продуктът е обновен успешно',
        'product_id' => $data['product_id']
    ]);

} catch (PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при обновяване на продукта',
        'error' => DEBUG_MODE ? $e->getMessage() : 'Моля опитайте отново'
    ]);
}
?>