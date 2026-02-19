<?php
// ============================================
// get_by_id.php - Детайли за поръчка
// GET: api/orders/get_by_id.php?id=1
// ============================================

require_once '../config.php';

if (!isset($_GET['id'])) {
    sendResponse(400, ['success' => false, 'message' => 'Липсва ID на поръчка']);
}

$order_id = intval($_GET['id']);

try {
    $conn = getDBConnection();

    // Основна информация за поръчката
    $sql = "
        SELECT 
            o.*,
            u.full_name,
            u.email,
            u.phone
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = :order_id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':order_id' => $order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        sendResponse(404, ['success' => false, 'message' => 'Поръчката не е намерена']);
    }

    // Продукти
    $sql_items = "
        SELECT 
            oi.*,
            p.model_name,
            p.main_image_url,
            b.brand_name
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.product_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        WHERE oi.order_id = :order_id
    ";
    
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->execute([':order_id' => $order_id]);
    $order['items'] = $stmt_items->fetchAll();

    // Услуги
    $sql_services = "
        SELECT 
            os.*,
            s.service_name,
            s.description,
            s.base_price as price,
            s.service_type
        FROM order_services os
        LEFT JOIN services s ON os.service_id = s.service_id
        WHERE os.order_id = :order_id
    ";
    
    $stmt_services = $conn->prepare($sql_services);
    $stmt_services->execute([':order_id' => $order_id]);
    $order['services'] = $stmt_services->fetchAll();

    // Плащане
    $sql_payment = "
        SELECT *
        FROM payments
        WHERE order_id = :order_id
        LIMIT 1
    ";
    
    $stmt_payment = $conn->prepare($sql_payment);
    $stmt_payment->execute([':order_id' => $order_id]);
    $order['payment'] = $stmt_payment->fetch();

    sendResponse(200, [
        'success' => true,
        'order' => $order
    ]);

} catch (PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при вземане на поръчката',
        'error' => DEBUG_MODE ? $e->getMessage() : 'Моля опитайте отново'
    ]);
}
?>