<?php
// ============================================
// service_requests/create.php - Create Service Request
// POST: api/service_requests/create.php
// Creates repair/installation/consultation requests
// ============================================

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, ['success' => false, 'message' => 'Позволен е само POST метод']);
}

$data = json_decode(file_get_contents('php://input'), true);

// Debug log
error_log('Service Request Data: ' . print_r($data, true));

if (!$data) {
    sendResponse(400, ['success' => false, 'message' => 'Невалидни данни']);
}

// Validate required fields
if (empty($data['service_id'])) {
    sendResponse(400, ['success' => false, 'message' => 'Липсва service_id']);
}

if (empty($data['contact_phone'])) {
    sendResponse(400, ['success' => false, 'message' => 'Липсва телефон за контакт']);
}

if (empty($data['address'])) {
    sendResponse(400, ['success' => false, 'message' => 'Липсва адрес']);
}

if (empty($data['city'])) {
    sendResponse(400, ['success' => false, 'message' => 'Липсва град']);
}

if (empty($data['description'])) {
    sendResponse(400, ['success' => false, 'message' => 'Липсва описание']);
}

// Extract data
$service_id = intval($data['service_id']);
$contact_phone = trim($data['contact_phone']);
$address = trim($data['address']);
$city = trim($data['city']);
$description = trim($data['description']);
$preferred_date = !empty($data['preferred_date']) ? $data['preferred_date'] : null;
$preferred_time = !empty($data['preferred_time']) ? $data['preferred_time'] : null;
$full_name = !empty($data['full_name']) ? trim($data['full_name']) : null;

// Get user_id if logged in (optional)
$user_id = null;
if (!empty($data['user_id'])) {
    $user_id = intval($data['user_id']);
}

// product_id is optional
$product_id = null;
if (!empty($data['product_id'])) {
    $product_id = intval($data['product_id']);
}

try {
    $conn = getDBConnection();

    // Insert service request
    $sql = "
        INSERT INTO service_requests (
            user_id,
            service_id,
            address,
            city,
            contact_phone,
            description,
            preferred_date,
            preferred_time,
            request_status,
            created_at
        ) VALUES (
            :user_id,
            :service_id,
            :address,
            :city,
            :contact_phone,
            :description,
            :preferred_date,
            :preferred_time,
            'pending',
            NOW()
        )
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':service_id' => $service_id,
        ':address' => $address,
        ':city' => $city,
        ':contact_phone' => $contact_phone,
        ':description' => $description,
        ':preferred_date' => $preferred_date,
        ':preferred_time' => $preferred_time
    ]);

    $request_id = $conn->lastInsertId();

    sendResponse(201, [
        'success' => true,
        'message' => 'Заявката е създадена успешно',
        'request_id' => $request_id,
        'data' => [
            'request_id' => $request_id,
            'service_id' => $service_id,
            'contact_phone' => $contact_phone,
            'city' => $city,
            'status' => 'pending'
        ]
    ]);

} catch (PDOException $e) {
    error_log('Service Request Error: ' . $e->getMessage());
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при създаване на заявката',
        'error' => $e->getMessage()
    ]);
}
?>