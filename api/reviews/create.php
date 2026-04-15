<?php
// ============================================
// create.php - Добавяне на отзив (с модерация)
// ============================================

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, ['success' => false, 'message' => 'Позволен е само POST метод']);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    sendResponse(400, ['success' => false, 'message' => 'Невалидни данни']);
}

// Задължителни полета
$required = ['product_id', 'rating', 'reviewer_name'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        sendResponse(400, ['success' => false, 'message' => "Липсва поле: $field"]);
    }
}

$rating = intval($data['rating']);
if ($rating < 1 || $rating > 5) {
    sendResponse(400, ['success' => false, 'message' => 'Оценката трябва да е между 1 и 5']);
}

try {
    $conn = getDBConnection();

    $user_id = isset($data['user_id']) ? intval($data['user_id']) : null;

    $stmt = $conn->prepare("
        INSERT INTO reviews (
            product_id, user_id, rating, title,
            review_text, reviewer_name, is_verified_purchase, is_approved
        ) VALUES (
            :product_id, :user_id, :rating, :title,
            :review_text, :reviewer_name, :is_verified_purchase, FALSE
        )
    ");

    $stmt->execute([
        ':product_id'           => intval($data['product_id']),
        ':user_id'              => $user_id,
        ':rating'               => $rating,
        ':title'                => $data['title'] ?? null,
        ':review_text'          => $data['review_text'] ?? null,
        ':reviewer_name'        => $data['reviewer_name'],
        ':is_verified_purchase' => 0
    ]);

    sendResponse(201, [
        'success' => true,
        'message' => 'Благодарим за отзива! Той ще бъде публикуван след преглед от нашия екип.'
    ]);

} catch (PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при добавяне на отзив',
        'error'   => $e->getMessage()
    ]);
}
?>