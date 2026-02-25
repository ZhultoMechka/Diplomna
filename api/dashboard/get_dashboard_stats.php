<?php
// ============================================
// get_dashboard_stats.php - Dashboard статистики
// GET: api/dashboard/get_dashboard_stats.php
// ============================================

require_once '../config.php';

// Проверка дали е admin (по-късно)
// requireLogin();
// if ($_SESSION['user_type'] !== 'admin') {
//     sendResponse(403, ['success' => false, 'message' => 'Нямате права']);
// }

try {
    $conn = getDBConnection();

    // 1. Общ брой поръчки
    $sql_orders = "SELECT COUNT(*) as total_orders FROM orders";
    $stmt = $conn->query($sql_orders);
    $total_orders = $stmt->fetch()['total_orders'];

    // 2. Общи приходи
    $sql_revenue = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE order_status != 'cancelled'";
    $stmt = $conn->query($sql_revenue);
    $total_revenue = $stmt->fetch()['total_revenue'] ?? 0;

    // 3. Брой продукти
    $sql_products = "SELECT COUNT(*) as total_products FROM products WHERE is_active = 1";
    $stmt = $conn->query($sql_products);
    $total_products = $stmt->fetch()['total_products'];

    // 4. Активни поръчки (pending, confirmed, processing, shipped)
    $sql_active = "
        SELECT COUNT(*) as active_orders 
        FROM orders 
        WHERE order_status IN ('pending', 'confirmed', 'processing', 'shipped')
    ";
    $stmt = $conn->query($sql_active);
    $active_orders = $stmt->fetch()['active_orders'];

    // 5. Поръчки по статус
    $sql_by_status = "
        SELECT order_status, COUNT(*) as count
        FROM orders
        GROUP BY order_status
    ";
    $stmt = $conn->query($sql_by_status);
    $orders_by_status = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // 6. Последни 5 поръчки
    $sql_recent = "
        SELECT 
            o.order_id,
            o.total_amount,
            o.order_status,
            o.created_at,
            u.full_name
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        ORDER BY o.created_at DESC
        LIMIT 5
    ";
    $stmt = $conn->query($sql_recent);
    $recent_orders = $stmt->fetchAll();

    // 7. Топ 5 продукта (най-продавани)
    $sql_top_products = "
        SELECT 
            p.product_id,
            p.model_name,
            b.brand_name,
            SUM(oi.quantity) as total_sold,
            SUM(oi.subtotal) as total_revenue
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.product_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        GROUP BY p.product_id
        ORDER BY total_sold DESC
        LIMIT 5
    ";
    $stmt = $conn->query($sql_top_products);
    $top_products = $stmt->fetchAll();

    // 8. Продажби по дни (последните 7 дни)
    $sql_daily = "
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as orders,
            SUM(total_amount) as revenue
        FROM orders
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ";
    $stmt = $conn->query($sql_daily);
    $daily_stats = $stmt->fetchAll();

    sendResponse(200, [
        'success' => true,
        'stats' => [
            'total_orders' => intval($total_orders),
            'total_revenue' => floatval($total_revenue),
            'total_products' => intval($total_products),
            'active_orders' => intval($active_orders),
            'orders_by_status' => $orders_by_status
        ],
        'recent_orders' => $recent_orders,
        'top_products' => $top_products,
        'daily_stats' => $daily_stats
    ]);

} catch (PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при вземане на статистики',
        'error' => DEBUG_MODE ? $e->getMessage() : 'Моля опитайте отново'
    ]);
}
?>