<?php
// ============================================
// config.php - Конфигурация за връзка с базата данни
// Този файл се include-ва във всички API файлове
// ============================================

// Позволяваме CORS (Cross-Origin Resource Sharing)
// Това е нужно за да може JavaScript-а да прави заявки към API-то
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Ако е OPTIONS request (preflight), връщаме OK и спираме
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================
// НАСТРОЙКИ ЗА БАЗА ДАННИ
// Промени тези стойности според твоята конфигурация
// ============================================
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root'); 
define('DB_PASS', 'qwe123');
define('DB_NAME', 'klimatici_db');

// ============================================
// ФУНКЦИЯ ЗА ВРЪЗКА С БАЗАТА ДАННИ
// ============================================
function getDBConnection() {
    try {
        // Създаваме PDO (PHP Data Objects) връзка
        // PDO е модерен и сигурен начин за работа с бази данни в PHP
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );
        
        // Задаваме режим на грешки - ще хвърля exceptions ако нещо не е наред
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Задаваме как да връща резултатите - като асоциативен масив
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $conn;
        
    } catch(PDOException $e) {
        // Ако има грешка при свързване, връщаме JSON с грешката
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Грешка при свързване с базата данни',
            'error' => $e->getMessage()
        ]);
        exit();
    }
}

// ============================================
// ПОМОЩНА ФУНКЦИЯ ЗА ВАЛИДИРАНЕ НА ДАННИ
// ============================================
function validateRequired($data, $required_fields) {
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[] = "Полето '$field' е задължително";
        }
    }
    
    return $errors;
}

// ============================================
// ПОМОЩНА ФУНКЦИЯ ЗА ПРОВЕРКА НА ИМЕЙЛ
// ============================================
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ============================================
// ФУНКЦИЯ ЗА ПРОВЕРКА НА ТЕЛЕФОНЕН НОМЕР
// ============================================
function validatePhone($phone) {
    // Проверяваме дали телефонът съдържа само цифри, +, -, (), интервали
    return preg_match('/^[\d\s\+\-\(\)]+$/', $phone);
}

// ============================================
// ФУНКЦИЯ ЗА SANITIZE НА INPUT
// Премахва опасни символи и код
// ============================================
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// ============================================
// ФУНКЦИЯ ЗА ВЗЕМАНЕ НА JSON INPUT
// ============================================
function getJSONInput() {
    $json = file_get_contents('php://input');
    return json_decode($json, true);
}

// ============================================
// ФУНКЦИЯ ЗА ИЗПРАЩАНЕ НА JSON RESPONSE
// ============================================
function sendResponse($status_code, $data) {
    http_response_code($status_code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// ============================================
// SESSION MANAGEMENT
// ============================================
// Стартираме сесията ако не е стартирана
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Функция за проверка дали user е logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Функция за вземане на текущия user
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'user_id' => $_SESSION['user_id'],
            'email' => $_SESSION['email'],
            'full_name' => $_SESSION['full_name'],
            'user_type' => $_SESSION['user_type']
        ];
    }
    return null;
}

// Функция за проверка дали user е admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_type'] === 'admin';
}

// Функция за проверка дали user е employee
function isEmployee() {
    return isLoggedIn() && in_array($_SESSION['user_type'], ['employee', 'admin']);
}

// ============================================
// DEBUG MODE
// Постави на false когато deploy-ваш на production
// ============================================
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

?>