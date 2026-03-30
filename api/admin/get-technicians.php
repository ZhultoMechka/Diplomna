<?php
// ============================================
// get-technicians-DEBUG.php - Debug Version
// GET: api/admin/get-technicians-DEBUG.php
// ============================================

session_start();
header('Content-Type: application/json');

// DEBUG: Log session data
error_log("=== GET TECHNICIANS DEBUG ===");
error_log("Session data: " . json_encode($_SESSION));

// Само GET заявки
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'message' => 'Позволен е само GET метод',
        'debug' => [
            'method' => $_SERVER['REQUEST_METHOD']
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
            'user_id_set' => false,
            'user_type_set' => isset($_SESSION['user_type']),
            'hint' => 'Опитайте да logout и login отново'
        ]
    ]);
    exit;
}

if ($_SESSION['user_type'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Нямате права - не сте admin',
        'debug' => [
            'your_user_type' => $_SESSION['user_type'],
            'required_user_type' => 'admin',
            'user_id' => $_SESSION['user_id']
        ]
    ]);
    exit;
}

// ============================================
// ВЗЕМАМЕ ТЕХНИЦИТЕ ОТ БАЗАТА
// ============================================

require_once '../config.php';

try {
    $conn = getDBConnection();
    
    // Join users и employees таблици
    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.email,
            u.full_name,
            u.phone,
            u.is_active,
            u.created_at,
            e.employee_id,
            e.position,
            e.hire_date,
            e.is_available
        FROM users u
        INNER JOIN employees e ON u.user_id = e.user_id
        WHERE u.user_type = 'employee'
        ORDER BY u.created_at DESC
    ");
    
    $stmt->execute();
    $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Found " . count($technicians) . " technicians");
    
    // Форматираме данните
    $formatted = array_map(function($tech) {
        return [
            'user_id' => (int)$tech['user_id'],
            'employee_id' => (int)$tech['employee_id'],
            'full_name' => $tech['full_name'],
            'email' => $tech['email'],
            'phone' => $tech['phone'],
            'position' => $tech['position'],
            'hire_date' => $tech['hire_date'],
            'is_active' => (bool)$tech['is_active'],
            'is_available' => (bool)$tech['is_available'],
            'created_at' => $tech['created_at']
        ];
    }, $technicians);
    
    echo json_encode([
        'success' => true,
        'technicians' => $formatted,
        'count' => count($formatted),
        'debug' => [
            'query_executed' => true,
            'admin_user_id' => $_SESSION['user_id'],
            'admin_name' => $_SESSION['full_name'] ?? 'N/A',
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Грешка при зареждане на техниците',
        'debug' => [
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'hint' => 'Проверете database connection'
        ]
    ]);
}
?>