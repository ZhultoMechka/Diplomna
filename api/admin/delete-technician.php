<?php
// ============================================
// delete-technician.php - Изтриване на техник (САМО ADMIN)
// DELETE: api/admin/delete-technician.php?user_id=X
// ============================================

require_once '../config.php';

// Само DELETE заявки
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    sendResponse(405, [
        'success' => false,
        'message' => 'Позволен е само DELETE метод'
    ]);
}

// ============================================
// ПРОВЕРКА ЗА ADMIN ПРАВА
// ============================================

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    sendResponse(403, [
        'success' => false,
        'message' => 'Нямате права за тази операция'
    ]);
}

// ============================================
// ВЗЕМАМЕ USER_ID
// ============================================

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($user_id <= 0) {
    sendResponse(400, [
        'success' => false,
        'message' => 'Невалидно ID на техник'
    ]);
}

// Защита: не може да изтриеш самия себе си
if ($user_id == $_SESSION['user_id']) {
    sendResponse(400, [
        'success' => false,
        'message' => 'Не можете да изтриете собствения си акаунт'
    ]);
}

// ============================================
// ИЗТРИВАНЕ ОТ БАЗАТА
// ============================================

try {
    $conn = getDBConnection();
    
    // Проверка дали user съществува и е employee
    $stmt = $conn->prepare("
        SELECT u.user_id, u.full_name, u.user_type 
        FROM users u
        WHERE u.user_id = :user_id
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        sendResponse(404, [
            'success' => false,
            'message' => 'Потребител не е намерен'
        ]);
    }
    
    // Проверка дали е employee
    if ($user['user_type'] !== 'employee') {
        sendResponse(400, [
            'success' => false,
            'message' => 'Този потребител не е техник'
        ]);
    }
    
    // Изтриване на employee запис
    $stmt = $conn->prepare("DELETE FROM employees WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    // Изтриване на user запис
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    // Log
    error_log("Admin {$_SESSION['user_id']} deleted technician: {$user['full_name']} (ID: {$user_id})");
    
    sendResponse(200, [
        'success' => true,
        'message' => 'Техникът беше изтрит успешно'
    ]);
    
} catch(PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при изтриване на техник',
        'error' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}
?>