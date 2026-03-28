<?php
// ============================================
// GET CONVERSATIONS API (for technicians)
// ============================================
// Endpoint: api/chat/get_conversations.php
// Method: GET
// Params: technician_id (optional for testing)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    // Get parameter (optional - shows all conversations if not provided)
    $technician_id = isset($_GET['technician_id']) ? intval($_GET['technician_id']) : null;
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Get all conversations
    // Shows ALL conversations regardless of technician for now (easier for testing)
    // Can be filtered by technician later if needed
    
    $query = "
        SELECT 
            COALESCE(m.order_id, 0) as order_id,
            u.user_id as customer_id,
            u.full_name as customer_name,
            u.email as customer_email,
            u.phone as customer_phone,
            COUNT(m.message_id) as total_messages,
            COUNT(CASE WHEN m.is_read = 0 AND m.sender_id != ? THEN 1 END) as unread_count,
            MAX(m.sent_at) as last_message_at,
            (
                SELECT message_text 
                FROM chat_messages 
                WHERE order_id = COALESCE(m.order_id, 0)
                ORDER BY sent_at DESC 
                LIMIT 1
            ) as last_message_text,
            (
                SELECT sender_id
                FROM chat_messages 
                WHERE order_id = COALESCE(m.order_id, 0)
                ORDER BY sent_at DESC 
                LIMIT 1
            ) as last_sender_id
        FROM chat_messages m
        JOIN users u ON m.sender_id = u.user_id
        WHERE u.user_type IN ('customer', 'client')
        GROUP BY m.order_id, u.user_id
        HAVING total_messages > 0
        ORDER BY last_message_at DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$technician_id ?? 0]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Remove duplicates (same order_id)
    $uniqueConversations = [];
    $seenOrders = [];
    
    foreach ($conversations as $conv) {
        $orderId = $conv['order_id'];
        if (!isset($seenOrders[$orderId])) {
            $seenOrders[$orderId] = true;
            $uniqueConversations[] = $conv;
        }
    }
    
    // Format conversations
    foreach ($uniqueConversations as &$conv) {
        $conv['order_id'] = intval($conv['order_id']);
        $conv['total_messages'] = intval($conv['total_messages']);
        $conv['unread_count'] = intval($conv['unread_count']);
        $conv['last_sender_is_customer'] = intval($conv['last_sender_id']) !== ($technician_id ?? 0);
    }
    
    // Return conversations
    echo json_encode([
        'success' => true,
        'conversations' => $uniqueConversations,
        'total_count' => count($uniqueConversations)
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'conversations' => []
    ]);
}
?>