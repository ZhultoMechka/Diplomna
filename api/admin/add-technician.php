<?php
// ============================================
// add-technician-DEBUG.php - Debug Version
// POST: api/admin/add-technician-DEBUG.php
// ============================================

session_start();
header('Content-Type: application/json');

// DEBUG: Log session data
error_log("=== ADD TECHNICIAN DEBUG ===");
error_log("Session data: " . json_encode($_SESSION));
error_log("Method: " . $_SERVER['REQUEST_METHOD']);

// Приемаме само POST заявки
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Позволен е само POST метод',
        'debug' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'expected' => 'POST'
        ]
    ]);
    exit;
}

// ============================================
// ПРОВЕРКА ЗА SESSION
// ============================================

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Session липсва - не сте логнати',
        'debug' => [
            'session_exists' => isset($_SESSION),
            'user_id_set' => isset($_SESSION['user_id']),
            'user_type_set' => isset($_SESSION['user_type']),
            'session_data' => $_SESSION ?? []
        ]
    ]);
    exit;
}

if (!isset($_SESSION['user_type'])) {
    echo json_encode([
        'success' => false,
        'message' => 'user_type липсва в session',
        'debug' => [
            'user_id' => $_SESSION['user_id'],
            'user_type' => 'NOT SET',
            'session_data' => $_SESSION
        ]
    ]);
    exit;
}

// CRITICAL: Само admin може да добавя техници
if ($_SESSION['user_type'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Нямате права - не сте admin',
        'debug' => [
            'your_user_type' => $_SESSION['user_type'],
            'required_user_type' => 'admin',
            'user_id' => $_SESSION['user_id'],
            'full_name' => $_SESSION['full_name'] ?? 'N/A'
        ]
    ]);
    exit;
}

// ============================================
// ВЗЕМАМЕ ДАННИТЕ
// ============================================

$input = file_get_contents('php://input');
$data = json_decode($input, true);

error_log("Input data: " . $input);

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Невалидни JSON данни',
        'debug' => [
            'raw_input' => $input,
            'json_error' => json_last_error_msg()
        ]
    ]);
    exit;
}

// Задължителни полета
$required = ['email', 'password', 'full_name', 'phone'];
$missing = [];
foreach ($required as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    echo json_encode([
        'success' => false,
        'message' => 'Липсват задължителни полета',
        'debug' => [
            'missing_fields' => $missing,
            'received_fields' => array_keys($data)
        ]
    ]);
    exit;
}

// ============================================
// РАБОТА С БАЗАТА ДАННИ
// ============================================

require_once '../config.php';

try {
    $conn = getDBConnection();
    
    $email = trim($data['email']);
    $password = $data['password'];
    $full_name = trim($data['full_name']);
    $phone = trim($data['phone']);
    $position = isset($data['position']) ? trim($data['position']) : 'technician';
    
    // Проверка дали email вече съществува
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Потребител с този имейл вече съществува',
            'debug' => [
                'email' => $email
            ]
        ]);
        exit;
    }
    
    // Криптираме паролата
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Създаваме user запис
    $stmt = $conn->prepare("
        INSERT INTO users (email, password, full_name, phone, user_type, is_active) 
        VALUES (:email, :password, :full_name, :phone, 'employee', 1)
    ");
    
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':phone', $phone);
    
    $stmt->execute();
    
    $user_id = $conn->lastInsertId();
    
    // Създаваме employee запис
    $stmt = $conn->prepare("
        INSERT INTO employees (user_id, position, hire_date, is_available) 
        VALUES (:user_id, :position, CURDATE(), 1)
    ");
    
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':position', $position);
    
    $stmt->execute();
    
    $employee_id = $conn->lastInsertId();
    
    // Log на действието
    error_log("Admin {$_SESSION['user_id']} created technician: {$full_name} (ID: {$user_id})");
    
    // Успешен отговор
    echo json_encode([
        'success' => true,
        'message' => 'Техникът беше добавен успешно!',
        'technician' => [
            'user_id' => $user_id,
            'employee_id' => $employee_id,
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'position' => $position
        ],
        'debug' => [
            'admin_user_id' => $_SESSION['user_id'],
            'admin_name' => $_SESSION['full_name'] ?? 'N/A'
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Грешка при добавяне на техник',
        'debug' => [
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ]
    ]);
}
?>