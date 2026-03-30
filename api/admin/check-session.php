<?php
// ============================================
// check-session.php - Session Debug Tool
// URL: api/admin/check-session.php
// ============================================

session_start();
header('Content-Type: application/json');

// Debug session data
$debug = [
    'session_exists' => isset($_SESSION) && !empty($_SESSION),
    'session_data' => $_SESSION ?? [],
    'user_id' => $_SESSION['user_id'] ?? 'NOT SET',
    'user_type' => $_SESSION['user_type'] ?? 'NOT SET',
    'full_name' => $_SESSION['full_name'] ?? 'NOT SET',
    'email' => $_SESSION['email'] ?? 'NOT SET',
    'is_admin' => isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin',
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>