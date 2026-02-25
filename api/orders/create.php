<?php
// ============================================
// create.php - Създаване на нова поръчка
// POST: api/orders/create.php
// ============================================

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(405, ['success' => false, 'message' => 'Позволен е само POST метод']);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    sendResponse(400, ['success' => false, 'message' => 'Невалидни данни']);
}

// Задължителни полета
$required = ['full_name', 'phone', 'delivery_address', 'city', 'payment_method', 'items'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        sendResponse(400, ['success' => false, 'message' => "Липсва поле: $field"]);
    }
}

if (empty($data['items']) || !is_array($data['items'])) {
    sendResponse(400, ['success' => false, 'message' => 'Количката е празна']);
}

try {
    $conn = getDBConnection();
    $conn->beginTransaction();

    // Вземаме user_id ако е логнат (опционално)
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : null;

    // Изчисляваме общата сума
    $total_amount = floatval($data['total_amount']);

    // 1. Създаваме поръчката
    $sql_order = "
        INSERT INTO orders (
            user_id, 
            total_amount, 
            delivery_address, 
            delivery_city,
            delivery_postal_code,
            contact_phone,
            notes,
            order_status
        )
        VALUES (
            :user_id, 
            :total_amount, 
            :delivery_address,
            :delivery_city,
            :delivery_postal_code,
            :contact_phone,
            :notes,
            'pending'
        )
    ";

    $stmt = $conn->prepare($sql_order);
    $stmt->execute([
        ':user_id'               => $user_id,
        ':total_amount'          => $total_amount,
        ':delivery_address'      => $data['delivery_address'],
        ':delivery_city'         => $data['city'],
        ':delivery_postal_code'  => $data['postal_code'] ?? null,
        ':contact_phone'         => $data['phone'],
        ':notes'                 => $data['notes'] ?? null
    ]);

    $order_id = $conn->lastInsertId();

    // 2. Добавяме продуктите (order_items) - ПРАВИЛНИ КОЛОНИ!
    $sql_item = "
        INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal)
        VALUES (:order_id, :product_id, :quantity, :unit_price, :subtotal)
    ";

    foreach ($data['items'] as $item) {
        $unit_price = floatval($item['unitPrice']);
        $quantity = intval($item['quantity']);
        $subtotal = $unit_price * $quantity;

        $stmt = $conn->prepare($sql_item);
        $stmt->execute([
            ':order_id'    => $order_id,
            ':product_id'  => $item['product']['product_id'],
            ':quantity'    => $quantity,
            ':unit_price'  => $unit_price,
            ':subtotal'    => $subtotal
        ]);

        // 3. Добавяме услугите за всеки продукт (order_services)
        if (!empty($item['services'])) {
            $sql_service = "
                INSERT INTO order_services (order_id, service_id, quantity, service_price)
                VALUES (:order_id, :service_id, :quantity, :service_price)
            ";

            foreach ($item['services'] as $service) {
                $stmt_svc = $conn->prepare($sql_service);
                $stmt_svc->execute([
                    ':order_id'      => $order_id,
                    ':service_id'    => $service['service_id'],
                    ':quantity'      => $quantity, // Същото количество като продукта
                    ':service_price' => floatval($service['price'])
                ]);
            }
        }
    }

    // 4. Записваме плащането - ПРАВИЛНИ КОЛОНИ И ENUM VALUES!
    
    // Конвертираме payment_method към DB enum values
    $payment_method_map = [
        'cash' => 'cash_on_delivery',
        'bank' => 'bank_transfer',
        'card' => 'credit_card'
    ];
    
    $payment_method = $payment_method_map[$data['payment_method']] ?? 'cash_on_delivery';
    
    $sql_payment = "
        INSERT INTO payments (order_id, amount, payment_method, payment_status)
        VALUES (:order_id, :amount, :payment_method, :payment_status)
    ";

    $payment_status = ($data['payment_method'] === 'card') ? 'completed' : 'pending';

    $stmt = $conn->prepare($sql_payment);
    $stmt->execute([
        ':order_id'        => $order_id,
        ':amount'          => $total_amount,
        ':payment_method'  => $payment_method,
        ':payment_status'  => $payment_status
    ]);

    $conn->commit();

    // Връщаме успех с order_id
    sendResponse(200, [
        'success'  => true,
        'message'  => 'Поръчката е направена успешно!',
        'order_id' => $order_id
    ]);

} catch (PDOException $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при записване на поръчката',
        'error' => $e->getMessage()
    ]);
}
?>