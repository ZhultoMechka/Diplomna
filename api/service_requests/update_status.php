<?php
// api/service_requests/update_status.php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, ['success' => false, 'message' => 'Позволен е само POST метод']);
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['request_id']) || empty($data['status'])) {
    sendResponse(400, ['success' => false, 'message' => 'Липсва request_id или status']);
}

$allowed = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
if (!in_array($data['status'], $allowed)) {
    sendResponse(400, ['success' => false, 'message' => 'Невалиден статус']);
}

try {
    $conn = getDBConnection();

    $stmt = $conn->prepare("
        UPDATE service_requests 
        SET request_status = :status, updated_at = NOW()
        WHERE request_id = :id
    ");
    $stmt->execute([
        ':status' => $data['status'],
        ':id'     => intval($data['request_id'])
    ]);

    sendResponse(200, ['success' => true, 'message' => 'Статусът е обновен успешно']);

} catch (PDOException $e) {
    sendResponse(500, ['success' => false, 'message' => $e->getMessage()]);
}
?>