<?php
// ============================================
// update_order_status.php - Simple Status Update
// ============================================

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, ['success' => false, 'message' => 'Позволен е само POST метод']);
}

$data = json_decode(file_get_contents('php://input'), true);

// Debug log
error_log('Update Status Request: ' . print_r($data, true));

if (!$data) {
    sendResponse(400, ['success' => false, 'message' => 'Невалидни данни']);
}

// проверка на задължителните полета
if (empty($data['order_id'])) {
    sendResponse(400, ['success' => false, 'message' => 'Липсва order_id']);
}

if (empty($data['order_status'])) {
    sendResponse(400, ['success' => false, 'message' => 'Липсва order_status']);
}

$order_id = intval($data['order_id']);
$new_status = $data['order_status'];

// валидиране на статуса
$allowed_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!in_array($new_status, $allowed_statuses)) {
    sendResponse(400, ['success' => false, 'message' => 'Невалиден статус']);
}

try {
    $conn = getDBConnection();

    // Актуализиране на статуса на поръчката
    $sql = "UPDATE orders SET order_status = :order_status WHERE order_id = :order_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':order_status' => $new_status,
        ':order_id' => $order_id
    ]);

    if ($stmt->rowCount() > 0) {
        sendResponse(200, [
            'success' => true,
            'message' => 'Статусът е обновен успешно',
            'order_id' => $order_id,
            'new_status' => $new_status
        ]);
    } else {
        sendResponse(404, [
            'success' => false,
            'message' => 'Поръчката не е намерена'
        ]);
    }

} catch (PDOException $e) {
    error_log('Update Status Error: ' . $e->getMessage());
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при обновяване на статуса',
        'error' => $e->getMessage()
    ]);
}
?>