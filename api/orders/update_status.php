<?php
// ============================================
// update_status.php - Промяна на статус на поръчка
// POST: api/orders/update_status.php
// ============================================

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, ['success' => false, 'message' => 'Позволен е само POST метод']);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['order_id']) || !isset($data['status'])) {
    sendResponse(400, ['success' => false, 'message' => 'Липсват задължителни полета']);
}

$order_id = intval($data['order_id']);
$status = $data['status'];

// Валидни статуси (от enum в базата данни)
$valid_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

if (!in_array($status, $valid_statuses)) {
    sendResponse(400, ['success' => false, 'message' => 'Невалиден статус']);
}

try {
    $conn = getDBConnection();

    // Проверка дали поръчката съществува
    $check = $conn->prepare("SELECT order_id FROM orders WHERE order_id = :id");
    $check->execute([':id' => $order_id]);
    
    if (!$check->fetch()) {
        sendResponse(404, ['success' => false, 'message' => 'Поръчката не е намерена']);
    }

    // Обновяване на статуса
    $sql = "
        UPDATE orders 
        SET order_status = :status,
            updated_at = NOW()
        WHERE order_id = :order_id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':status' => $status,
        ':order_id' => $order_id
    ]);

    sendResponse(200, [
        'success' => true,
        'message' => 'Статусът е обновен успешно'
    ]);

} catch (PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при обновяване на статуса',
        'error' => DEBUG_MODE ? $e->getMessage() : 'Моля опитайте отново'
    ]);
}
?>