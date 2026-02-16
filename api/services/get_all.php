<?php
// ============================================
// get_all.php - Вземане на всички услуги
// GET: api/services/get_all.php
// ============================================

require_once '../config.php';

// Приемаме само GET заявки
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(405, [
        'success' => false,
        'message' => 'Позволен е само GET метод'
    ]);
}

try {
    $conn = getDBConnection();
    
    // Проверка какви колони има в таблицата
    $columnsQuery = $conn->query("SHOW COLUMNS FROM services");
    $columns = $columnsQuery->fetchAll(PDO::FETCH_COLUMN);
    
    // Намери името на ценовата колона
    $priceColumn = 'price'; // default
    foreach ($columns as $col) {
        if (stripos($col, 'price') !== false || stripos($col, 'cost') !== false || stripos($col, 'amount') !== false) {
            $priceColumn = $col;
            break;
        }
    }
    
    // Вземаме всички услуги с динамично име на ценовата колона
    $sql = "
        SELECT 
            service_id,
            service_name,
            description,
            $priceColumn as price
        FROM services
        ORDER BY service_name ASC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $services = $stmt->fetchAll();
    
    // Добавяме is_active поле за всички услуги (всички са активни)
    foreach ($services as &$service) {
        $service['is_active'] = 1;
    }
    
    // Форматираме отговора
    sendResponse(200, [
        'success' => true,
        'count' => count($services),
        'services' => $services,
        'debug_price_column' => DEBUG_MODE ? $priceColumn : null
    ]);
    
} catch(PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при вземане на услугите',
        'error' => DEBUG_MODE ? $e->getMessage() : 'Моля опитайте отново'
    ]);
}
?>