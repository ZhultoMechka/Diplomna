<?php
// ============================================
// service_requests/get_all.php - Get All Service Requests
// ============================================

require_once '../config.php';

try {
    $conn = getDBConnection();

    // Get all service requests with service info
    $sql = "
        SELECT 
            sr.request_id,
            sr.user_id,
            sr.service_id,
            sr.address,
            sr.city,
            sr.contact_phone,
            sr.description,
            sr.preferred_date,
            sr.preferred_time,
            sr.request_status,
            sr.created_at,
            sr.updated_at,
            s.service_name,
            s.service_type,
            u.full_name as customer_name,
            u.email as customer_email
        FROM service_requests sr
        INNER JOIN services s ON sr.service_id = s.service_id
        LEFT JOIN users u ON sr.user_id = u.user_id
        ORDER BY 
            CASE sr.request_status
                WHEN 'pending' THEN 1
                WHEN 'confirmed' THEN 2
                WHEN 'in_progress' THEN 3
                WHEN 'completed' THEN 4
                WHEN 'cancelled' THEN 5
                ELSE 6
            END,
            sr.created_at DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $requests = $stmt->fetchAll();

    // Calculate statistics
    $stats = [
        'total' => count($requests),
        'pending' => 0,
        'confirmed' => 0,
        'in_progress' => 0,
        'completed' => 0,
        'cancelled' => 0
    ];

    foreach ($requests as $request) {
        $status = $request['request_status'];
        if (isset($stats[$status])) {
            $stats[$status]++;
        }
    }

    sendResponse(200, [
        'success' => true,
        'requests' => $requests,
        'stats' => $stats,
        'count' => count($requests)
    ]);

} catch (PDOException $e) {
    error_log('Get Service Requests Error: ' . $e->getMessage());
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при зареждане на заявките',
        'error' => $e->getMessage()
    ]);
}
?>