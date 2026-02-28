<?php
// ============================================
// create.php - Добавяне на отзив
// POST: api/reviews/create.php
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

// Валидация на rating
$rating = intval($data['rating']);
if ($rating < 1 || $rating > 5) {
    sendResponse(400, ['success' => false, 'message' => 'Оценката трябва да е между 1 и 5']);
}

try {
    $conn = getDBConnection();

    // Вземаме user_id ако е логнат
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : null;

    // Проверка дали продуктът съществува
    $check = $conn->prepare("SELECT product_id FROM products WHERE product_id = :id");
    $check->execute([':id' => $data['product_id']]);
    
    if (!$check->fetch()) {
        sendResponse(404, ['success' => false, 'message' => 'Продуктът не съществува']);
    }

    // Проверка дали user-а вече е оставил отзив за този продукт (optional)
    if ($user_id) {
        $checkReview = $conn->prepare("
            SELECT review_id 
            FROM reviews 
            WHERE product_id = :product_id AND user_id = :user_id
        ");
        $checkReview->execute([
            ':product_id' => $data['product_id'],
            ':user_id' => $user_id
        ]);
        
        if ($checkReview->fetch()) {
            sendResponse(400, ['success' => false, 'message' => 'Вече сте оставили отзив за този продукт']);
        }
    }

    // Вмъкване на отзив
    $sql = "
        INSERT INTO reviews (
            product_id,
            user_id,
            rating,
            title,
            review_text,
            reviewer_name,
            is_verified_purchase
        ) VALUES (
            :product_id,
            :user_id,
            :rating,
            :title,
            :review_text,
            :reviewer_name,
            :is_verified_purchase
        )
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':product_id'           => intval($data['product_id']),
        ':user_id'              => $user_id,
        ':rating'               => $rating,
        ':title'                => $data['title'] ?? null,
        ':review_text'          => $data['review_text'] ?? null,
        ':reviewer_name'        => $data['reviewer_name'],
        ':is_verified_purchase' => isset($data['is_verified_purchase']) ? $data['is_verified_purchase'] : false
    ]);

    $review_id = $conn->lastInsertId();

    sendResponse(201, [
        'success' => true,
        'message' => 'Отзивът е добавен успешно!',
        'review_id' => $review_id
    ]);

} catch (PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при добавяне на отзив',
        'error' => $e->getMessage()
    ]);
}
?>