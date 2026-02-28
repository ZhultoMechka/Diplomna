<?php
// ============================================
// get_by_product.php - Отзиви за продукт
// GET: api/reviews/get_by_product.php?product_id=1
// ============================================

require_once '../config.php';

if (!isset($_GET['product_id'])) {
    sendResponse(400, ['success' => false, 'message' => 'Липсва ID на продукт']);
}

$product_id = intval($_GET['product_id']);

try {
    $conn = getDBConnection();

    // Проверка дали продуктът съществува
    $check = $conn->prepare("SELECT product_id FROM products WHERE product_id = :id");
    $check->execute([':id' => $product_id]);
    
    if (!$check->fetch()) {
        sendResponse(404, ['success' => false, 'message' => 'Продуктът не съществува']);
    }

    // Вземаме всички одобрени отзиви за продукта
    $sql = "
        SELECT 
            review_id,
            product_id,
            user_id,
            rating,
            title,
            review_text,
            reviewer_name,
            is_verified_purchase,
            created_at
        FROM reviews
        WHERE product_id = :product_id 
        AND is_approved = TRUE
        ORDER BY created_at DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':product_id' => $product_id]);
    $reviews = $stmt->fetchAll();

    // Изчисляване на статистика
    $sql_stats = "
        SELECT 
            COUNT(*) as total_reviews,
            AVG(rating) as average_rating,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_stars,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_stars,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_stars,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_stars,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM reviews
        WHERE product_id = :product_id 
        AND is_approved = TRUE
    ";

    $stmt_stats = $conn->prepare($sql_stats);
    $stmt_stats->execute([':product_id' => $product_id]);
    $stats = $stmt_stats->fetch();

    sendResponse(200, [
        'success' => true,
        'reviews' => $reviews,
        'stats' => [
            'total_reviews' => intval($stats['total_reviews']),
            'average_rating' => round(floatval($stats['average_rating']), 1),
            'five_stars' => intval($stats['five_stars']),
            'four_stars' => intval($stats['four_stars']),
            'three_stars' => intval($stats['three_stars']),
            'two_stars' => intval($stats['two_stars']),
            'one_star' => intval($stats['one_star'])
        ]
    ]);

} catch (PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при вземане на отзиви',
        'error' => $e->getMessage()
    ]);
}
?>