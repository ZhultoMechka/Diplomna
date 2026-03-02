<?php
// ============================================
// get_technician_orders.php - Orders for Technicians
// GET: api/orders/get_technician_orders.php
// Returns only orders with services (installation, repair, maintenance)
// ============================================

require_once '../config.php';

try {
    $conn = getDBConnection();

    // Get all orders with services
    $sql = "
        SELECT 
            o.order_id,
            o.user_id,
            u.full_name as customer_name,
            u.phone as customer_phone,
            o.delivery_address,
            o.delivery_city,
            o.delivery_postal_code,
            o.order_status,
            o.total_amount,
            o.created_at,
            o.updated_at
        FROM orders o
        INNER JOIN order_services os ON o.order_id = os.order_id
        LEFT JOIN users u ON o.user_id = u.user_id
        GROUP BY o.order_id
        ORDER BY 
            CASE o.order_status
                WHEN 'confirmed' THEN 1
                WHEN 'processing' THEN 2
                WHEN 'shipped' THEN 3
                WHEN 'delivered' THEN 4
                WHEN 'pending' THEN 5
                ELSE 6
            END,
            o.created_at DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll();

    // For each order, get services
    foreach ($orders as &$order) {
        // Get services
        $services_sql = "
            SELECT 
                os.order_service_id,
                os.service_id,
                s.service_name,
                s.service_type,
                os.service_price,
                os.quantity
            FROM order_services os
            INNER JOIN services s ON os.service_id = s.service_id
            WHERE os.order_id = :order_id
        ";
        $services_stmt = $conn->prepare($services_sql);
        $services_stmt->execute([':order_id' => $order['order_id']]);
        $order['services'] = $services_stmt->fetchAll();

        // Get products count
        $products_sql = "
            SELECT COUNT(*) as product_count
            FROM order_items
            WHERE order_id = :order_id
        ";
        $products_stmt = $conn->prepare($products_sql);
        $products_stmt->execute([':order_id' => $order['order_id']]);
        $order['product_count'] = $products_stmt->fetch()['product_count'];
    }

    // Calculate statistics
    $stats = [
        'total' => count($orders),
        'pending' => 0,
        'confirmed' => 0,
        'processing' => 0,
        'shipped' => 0,
        'delivered' => 0,
        'cancelled' => 0
    ];

    foreach ($orders as $order) {
        $status = $order['order_status'];
        if (isset($stats[$status])) {
            $stats[$status]++;
        }
    }

    sendResponse(200, [
        'success' => true,
        'orders' => $orders,
        'stats' => $stats,
        'count' => count($orders)
    ]);

} catch (PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при зареждане на поръчките',
        'error' => $e->getMessage()
    ]);
}
?>