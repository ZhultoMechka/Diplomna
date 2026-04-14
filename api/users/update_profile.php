<?php
// ============================================
// update_profile.php - Обновяване на профил
// POST: api/users/update_profile.php
// ============================================

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, ['success' => false, 'message' => 'Позволен е само POST метод']);
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['user_id'])) {
    sendResponse(400, ['success' => false, 'message' => 'Липсва user_id']);
}
if (empty($data['full_name'])) {
    sendResponse(400, ['success' => false, 'message' => 'Името е задължително']);
}
if (empty($data['email'])) {
    sendResponse(400, ['success' => false, 'message' => 'Имейлът е задължителен']);
}

try {
    $conn = getDBConnection();

    // Проверка дали имейлът не е зает от друг потребител
    $check = $conn->prepare("
        SELECT user_id FROM users 
        WHERE email = :email AND user_id != :user_id
    ");
    $check->execute([':email' => $data['email'], ':user_id' => $data['user_id']]);
    if ($check->fetch()) {
        sendResponse(400, ['success' => false, 'message' => 'Този имейл вече се използва от друг акаунт.']);
    }

    {
        // Без смяна на парола
        $stmt = $conn->prepare("
            UPDATE users 
            SET full_name = :full_name,
                phone     = :phone,
                email     = :email,
                updated_at = NOW()
            WHERE user_id = :user_id
        ");
        $stmt->execute([
            ':full_name' => $data['full_name'],
            ':phone'     => $data['phone'] ?? null,
            ':email'     => $data['email'],
            ':user_id'   => intval($data['user_id'])
        ]);
    }

    sendResponse(200, [
        'success' => true,
        'message' => 'Профилът е обновен успешно!'
    ]);

} catch (PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при обновяване на профила.',
        'error'   => $e->getMessage()
    ]);
}
?>