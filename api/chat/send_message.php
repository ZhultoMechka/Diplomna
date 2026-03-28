<?php
// ============================================
// SEND MESSAGE API
// ============================================
// Endpoint: api/chat/send_message.php
// Method: POST
// Body: { order_id, sender_id, message_text }

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (!isset($input['order_id']) || !isset($input['sender_id']) || !isset($input['message_text'])) {
        throw new Exception('Missing required fields: order_id, sender_id, message_text');
    }
    
    $order_id = intval($input['order_id']);
    $sender_id = intval($input['sender_id']);
    $message_text = trim($input['message_text']);
    
    if (empty($message_text)) {
        throw new Exception('Message text cannot be empty');
    }
    
    if (strlen($message_text) > 1000) {
        throw new Exception('Message too long (max 1000 characters)');
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Verify that order exists (skip for order_id = 0 which means general inquiry)
    if ($order_id > 0) {
        $check_order = $db->prepare("SELECT order_id FROM orders WHERE order_id = ?");
        $check_order->execute([$order_id]);
        if ($check_order->rowCount() === 0) {
            throw new Exception('Order not found');
        }
    }
    
    // Verify that sender exists
    $check_user = $db->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $check_user->execute([$sender_id]);
    if ($check_user->rowCount() === 0) {
        throw new Exception('User not found');
    }
    
    // Insert message
    $insert = $db->prepare("
        INSERT INTO chat_messages (order_id, sender_id, message_text, sent_at) 
        VALUES (?, ?, ?, NOW())
    ");
    
    $insert->execute([$order_id, $sender_id, $message_text]);
    
    $message_id = $db->lastInsertId();
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully',
        'message_id' => $message_id
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>