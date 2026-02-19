<?php
// ============================================
// delete.php - Изтриване на продукт
// DELETE/POST: api/products/delete.php
// ============================================

require_once '../config.php';

// Приемаме POST или DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    sendResponse(405, ['success' => false, 'message' => 'Позволени са само POST/DELETE методи']);
}

// Проверка дали е admin (по-късно)
// requireLogin();
// if ($_SESSION['user_type'] !== 'admin') {
//     sendResponse(403, ['success' => false, 'message' => 'Нямате права за това действие']);
// }

$data = json_decode(file_get_contents('php://input'), true);

// Алтернативно от query параметър
$product_id = $data['product_id'] ?? $_GET['id'] ?? null;

if (!$product_id) {
    sendResponse(400, ['success' => false, 'message' => 'Липсва ID на продукт']);
}

$product_id = intval($product_id);

try {
    $conn = getDBConnection();

    // Проверка дали продуктът съществува
    $check = $conn->prepare("SELECT product_id, model_name FROM products WHERE product_id = :id");
    $check->execute([':id' => $product_id]);
    $product = $check->fetch();
    
    if (!$product) {
        sendResponse(404, ['success' => false, 'message' => 'Продуктът не съществува']);
    }

    // Проверка дали продуктът е в някоя поръчка
    $checkOrders = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE product_id = :id");
    $checkOrders->execute([':id' => $product_id]);
    $orderCount = $checkOrders->fetch()['count'];

    if ($orderCount > 0) {
        // Ако е използван в поръчки - деактивираме го вместо да го изтриваме
        $sql = "UPDATE products SET is_active = 0, updated_at = NOW() WHERE product_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $product_id]);

        sendResponse(200, [
            'success' => true,
            'message' => 'Продуктът е деактивиран (използван в поръчки)',
            'deactivated' => true
        ]);
    } else {
        // Ако не е използван - изтриваме го напълно
        $sql = "DELETE FROM products WHERE product_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $product_id]);

        sendResponse(200, [
            'success' => true,
            'message' => 'Продуктът е изтрит успешно',
            'deleted' => true
        ]);
    }

} catch (PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при изтриване на продукта',
        'error' => DEBUG_MODE ? $e->getMessage() : 'Моля опитайте отново'
    ]);
}
?>