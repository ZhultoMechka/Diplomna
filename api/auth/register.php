<?php
// ============================================
// register.php - Регистрация на нов потребител
// PUT: api/auth/register.php
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
$required_fields = ['email', 'password', 'full_name', 'phone', 'user_type'];
$errors = validateRequired($data, $required_fields);

if (!empty($errors)) {
    sendResponse(400, [
        'success' => false,
        'message' => 'Моля попълнете всички задължителни полета',
        'errors' => $errors
    ]);
}

// Sanitize входните данни
$email = sanitizeInput($data['email']);
$password = $data['password'];
$full_name = sanitizeInput($data['full_name']);
$phone = sanitizeInput($data['phone']);
$user_type = sanitizeInput($data['user_type']);

// Валидираме имейла
if (!validateEmail($email)) {
    sendResponse(400, [
        'success' => false,
        'message' => 'Невалиден имейл адрес'
    ]);
}

// Валидираме телефона
if (!validatePhone($phone)) {
    sendResponse(400, [
        'success' => false,
        'message' => 'Невалиден телефонен номер'
    ]);
}

// Проверяваме типа потребител
if (!in_array($user_type, ['client', 'employee', 'admin'])) {
    sendResponse(400, [
        'success' => false,
        'message' => 'Невалиден тип потребител'
    ]);
}

// Проверяваме дължината на паролата
if (strlen($password) < 6) {
    sendResponse(400, [
        'success' => false,
        'message' => 'Паролата трябва да е минимум 6 символа'
    ]);
}

// ============================================
// РАБОТА С БАЗАТА ДАННИ
// ============================================

try {
    $conn = getDBConnection();
    
    // Проверяваме дали имейлът вече съществува
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        sendResponse(409, [
            'success' => false,
            'message' => 'Този имейл вече е регистриран'
        ]);
    }
    
    // Криптираме паролата с bcrypt (най-сигурният метод)
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Вмъкваме новия потребител в базата
    $stmt = $conn->prepare("
        INSERT INTO users (email, password, full_name, phone, user_type, is_active) 
        VALUES (:email, :password, :full_name, :phone, :user_type, 1)
    ");
    
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':user_type', $user_type);
    
    $stmt->execute();
    
    // Вземаме ID на новосъздадения потребител
    $user_id = $conn->lastInsertId();
    
    // ============================================
    // СЪЗДАВАМЕ ДОПЪЛНИТЕЛЕН ЗАПИС СПОРЕД ТИПА
    // ============================================
    
    if ($user_type === 'client') {
        // Ако е клиент, създаваме запис в clients таблицата
        $stmt = $conn->prepare("
            INSERT INTO clients (user_id, receive_promotions) 
            VALUES (:user_id, 1)
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
    } elseif ($user_type === 'employee' || $user_type === 'admin') {
        // Ако е служител, създаваме запис в employees таблицата
        $position = isset($data['position']) ? sanitizeInput($data['position']) : 'customer_service';
        
        $stmt = $conn->prepare("
            INSERT INTO employees (user_id, position, hire_date, is_available) 
            VALUES (:user_id, :position, CURDATE(), 1)
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':position', $position);
        $stmt->execute();
    }
    
    // ============================================
    // АВТОМАТИЧНО ВЛИЗАНЕ СЛЕД РЕГИСТРАЦИЯ
    // ============================================
    
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $email;
    $_SESSION['full_name'] = $full_name;
    $_SESSION['user_type'] = $user_type;
    
    // Връщаме успешен отговор
    sendResponse(201, [
        'success' => true,
        'message' => 'Регистрацията беше успешна!',
        'user' => [
            'user_id' => $user_id,
            'email' => $email,
            'full_name' => $full_name,
            'user_type' => $user_type
        ]
    ]);
    
} catch(PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при регистрацията',
        'error' => DEBUG_MODE ? $e->getMessage() : 'Моля опитайте отново'
    ]);
}
?>