<?php
// ============================================
// get_all.php - Всички марки
// GET: api/brands/get_all.php
// ============================================

require_once '../config.php';

try {
    $conn = getDBConnection();

    $stmt = $conn->prepare("
        SELECT brand_id, brand_name 
        FROM brands 
        ORDER BY brand_name ASC
    ");
    $stmt->execute();
    $brands = $stmt->fetchAll();

    sendResponse(200, [
        'success' => true,
        'brands'  => $brands,
        'count'   => count($brands)
    ]);

} catch (PDOException $e) {
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при зареждане на марките',
        'error'   => $e->getMessage()
    ]);
}
?>