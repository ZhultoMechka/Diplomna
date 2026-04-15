<?php
// ============================================
// get_all.php - Всички поръчки
// ============================================

require_once '../config.php';

try {
    $conn = getDBConnection();

    // Взимаме всички поръчки с информация за клиента
    $sql = "
        SELECT 
            o.order_id,
            o.user_id,
            o.total_amount,
            o.delivery_address,
            o.delivery_city,
            o.delivery_postal_code,
            o.contact_phone,
            o.notes,
            o.order_status as status,
            o.created_at,
            u.full_name,
            u.email,
            u.phone,
            (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as items_count
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        ORDER BY o.created_at DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll();

    // За всяка поръчка вземаме продуктите и услугите
    foreach ($orders as &$order) {
        // Продукти
        $sql_items = "
            SELECT 
                oi.quantity,
                oi.unit_price,
                oi.subtotal,
                p.model_name,
                p.main_image_url,
                b.brand_name
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.product_id
            LEFT JOIN brands b ON p.brand_id = b.brand_id
            WHERE oi.order_id = :order_id
        ";
        
        $stmt_items = $conn->prepare($sql_items);
        $stmt_items->execute([':order_id' => $order['order_id']]);
        $order['items'] = $stmt_items->fetchAll();

        // Услуги
        $sql_services = "
            SELECT 
                s.service_name,
                os.service_price as price
            FROM order_services os
            LEFT JOIN services s ON os.service_id = s.service_id
            WHERE os.order_id = :order_id
        ";
        
        $stmt_services = $conn->prepare($sql_services);
        $stmt_services->execute([':order_id' => $order['order_id']]);
        $order['services'] = $stmt_services->fetchAll();

        // Плащане
        $sql_payment = "
            SELECT payment_method, payment_status
            FROM payments
            WHERE order_id = :order_id
            LIMIT 1
        ";
        
        $stmt_payment = $conn->prepare($sql_payment);
        $stmt_payment->execute([':order_id' => $order['order_id']]);
        $payment = $stmt_payment->fetch();
        
        $order['payment_method'] = $payment ? $payment['payment_method'] : null;
        $order['payment_status'] = $payment ? $payment['payment_status'] : null;
    }

    sendResponse(200, [
        'success' => true,
        'count' => count($orders),
        'orders' => $orders
    ]);

} catch (PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при вземане на поръчките',
        'error' => $e->getMessage()
    ]);
}
?>