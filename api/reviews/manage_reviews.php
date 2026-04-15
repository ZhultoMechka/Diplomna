<?php
// ============================================
// manage_reviews.php - Управление на отзиви
// ============================================

require_once '../config.php';

//GET: Вземи всички неодобрени отзиви
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $conn = getDBConnection();

        $stmt = $conn->prepare("
            SELECT 
                r.review_id,
                r.rating,
                r.title,
                r.review_text,
                r.reviewer_name,
                r.is_approved,
                r.created_at,
                p.model_name   AS product_name,
                p.product_id
            FROM reviews r
            JOIN products p ON r.product_id = p.product_id
            WHERE r.is_approved = FALSE
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        $reviews = $stmt->fetchAll();

        sendResponse(200, [
            'success' => true,
            'reviews' => $reviews,
            'count'   => count($reviews)
        ]);

    } catch (PDOException $e) {
        sendResponse(500, ['success' => false, 'message' => $e->getMessage()]);
    }
}

//POST: Одобри или отхвърли отзив
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['review_id']) || empty($data['action'])) {
        sendResponse(400, ['success' => false, 'message' => 'Липсва review_id или action']);
    }

    $action = $data['action']; // 'approve' или 'reject'

    try {
        $conn = getDBConnection();

        if ($action === 'approve') {
            $stmt = $conn->prepare("UPDATE reviews SET is_approved = TRUE WHERE review_id = :id");
            $stmt->execute([':id' => intval($data['review_id'])]);
            sendResponse(200, ['success' => true, 'message' => 'Отзивът е одобрен и публикуван.']);

        } elseif ($action === 'reject') {
            $stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = :id");
            $stmt->execute([':id' => intval($data['review_id'])]);
            sendResponse(200, ['success' => true, 'message' => 'Отзивът е отхвърлен и изтрит.']);

        } else {
            sendResponse(400, ['success' => false, 'message' => 'Невалидно действие.']);
        }

    } catch (PDOException $e) {
        sendResponse(500, ['success' => false, 'message' => $e->getMessage()]);
    }
}
?>