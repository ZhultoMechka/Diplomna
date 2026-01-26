<?php
// ============================================
// login.php - Влизане в системата
// POST: api/auth/login.php
// ============================================

require_once '../config.php';

// Приемаме само POST заявки
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, [
        'success' => false,
        'message' => 'Позволен е само POST метод'
    ]);
}

// Вземаме JSON данните от request-а
$data = getJSONInput();

// ============================================
// ВАЛИДАЦИЯ НА ВХОДНИТЕ ДАННИ
// ============================================

// Проверяваме задължителните полета
$required_fields = ['email', 'password'];
$errors = validateRequired($data, $required_fields);

if (!empty($errors)) {
    sendResponse(400, [
        'success' => false,
        'message' => 'Моля попълнете имейл и парола',
        'errors' => $errors
    ]);
}

$email = sanitizeInput($data['email']);
$password = $data['password'];

// Валидираме имейла
if (!validateEmail($email)) {
    sendResponse(400, [
        'success' => false,
        'message' => 'Невалиден имейл адрес'
    ]);
}

// ============================================
// ПРОВЕРКА В БАЗАТА ДАННИ
// ============================================

try {
    $conn = getDBConnection();
    
    // Вземаме потребителя по имейл
    $stmt = $conn->prepare("
        SELECT user_id, email, password, full_name, phone, user_type, is_active 
        FROM users 
        WHERE email = :email
    ");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    // Проверяваме дали потребителят съществува
    if ($stmt->rowCount() === 0) {
        sendResponse(401, [
            'success' => false,
            'message' => 'Грешен имейл или парола'
        ]);
    }
    
    $user = $stmt->fetch();
    
    // Проверяваме дали акаунтът е активен
    if (!$user['is_active']) {
        sendResponse(403, [
            'success' => false,
            'message' => 'Вашият акаунт е деактивиран. Моля свържете се с администратор.'
        ]);
    }
    
    // Проверяваме паролата
    if (!password_verify($password, $user['password'])) {
        sendResponse(401, [
            'success' => false,
            'message' => 'Грешен имейл или парола'
        ]);
    }
    
    // ============================================
    // УСПЕШНО ВЛИЗАНЕ - СЪЗДАВАМЕ СЕСИЯ
    // ============================================
    
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['user_type'] = $user['user_type'];
    
    // Ако е клиент, вземаме и клиентската информация
    $additional_info = null;
    if ($user['user_type'] === 'client') {
        $stmt = $conn->prepare("
            SELECT delivery_address, city, postal_code, company_name 
            FROM clients 
            WHERE user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $user['user_id']);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $additional_info = $stmt->fetch();
        }
    } 
    // Ако е служител, вземаме служителската информация
    elseif ($user['user_type'] === 'employee' || $user['user_type'] === 'admin') {
        $stmt = $conn->prepare("
            SELECT position, specialization, rating 
            FROM employees 
            WHERE user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $user['user_id']);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $additional_info = $stmt->fetch();
            $_SESSION['position'] = $additional_info['position'];
        }
    }
    
    // Актуализираме последното влизане (можем да добавим поле last_login в users таблицата)
    // $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :user_id");
    // $stmt->bindParam(':user_id', $user['user_id']);
    // $stmt->execute();
    
    // Връщаме успешен отговор
    sendResponse(200, [
        'success' => true,
        'message' => 'Успешно влизане!',
        'user' => [
            'user_id' => $user['user_id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'phone' => $user['phone'],
            'user_type' => $user['user_type'],
            'additional_info' => $additional_info
        ]
    ]);
    
} catch(PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при влизане',
        'error' => DEBUG_MODE ? $e->getMessage() : 'Моля опитайте отново'
    ]);
}
?>