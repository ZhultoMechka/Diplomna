<?php
// ============================================
// GET MESSAGES API
// ============================================


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    // взимат се параметрите
    if (!isset($_GET['order_id'])) {
        throw new Exception('Missing required parameter: order_id');
    }
    
    $order_id = intval($_GET['order_id']);
    $after_id = isset($_GET['after_id']) ? intval($_GET['after_id']) : 0;
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    
    // Връзна с базата данни
    $database = new Database();
    $db = $database->getConnection();
    
    // Взима съобщенията
    $query = "
        SELECT 
            m.message_id,
            m.order_id,
            m.sender_id,
            m.message_text,
            m.sent_at,
            m.is_read,
            u.full_name as sender_name,
            u.user_type as sender_type
        FROM chat_messages m
        JOIN users u ON m.sender_id = u.user_id
        WHERE m.order_id = ?
        AND m.message_id > ?
        ORDER BY m.sent_at ASC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$order_id, $after_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Бройка непрочетени съобщения за user_id 
    $unread_count = 0;
    if ($user_id) {
        $unread_query = "
            SELECT COUNT(*) as unread_count
            FROM chat_messages
            WHERE order_id = ?
            AND sender_id != ?
            AND is_read = 0
        ";
        $unread_stmt = $db->prepare($unread_query);
        $unread_stmt->execute([$order_id, $user_id]);
        $unread_result = $unread_stmt->fetch(PDO::FETCH_ASSOC);
        $unread_count = intval($unread_result['unread_count']);
        
        // маркираме тези съобщения като прочетени
        $mark_read = $db->prepare("
            UPDATE chat_messages 
            SET is_read = 1 
            WHERE order_id = ? 
            AND sender_id != ?
            AND is_read = 0
        ");
        $mark_read->execute([$order_id, $user_id]);
    }
    
    // връща съобщенията
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'unread_count' => $unread_count,
        'total_count' => count($messages)
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'messages' => []
    ]);
}
?>