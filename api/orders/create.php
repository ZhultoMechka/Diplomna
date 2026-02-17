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

    // Пълен адрес
    $full_address = $data['delivery_address'] . ', ' . $data['city'];
    if (!empty($data['postal_code'])) {
        $full_address .= ', ' . $data['postal_code'];
    }

    // 1. Създаваме поръчката
    $sql_order = "
        INSERT INTO orders (user_id, total_amount, delivery_address, status, created_at)
        VALUES (:user_id, :total_amount, :delivery_address, 'pending', NOW())
    ";

    $stmt = $conn->prepare($sql_order);
    $stmt->execute([
        ':user_id'          => $user_id,
        ':total_amount'     => $total_amount,
        ':delivery_address' => $full_address
    ]);

    $order_id = $conn->lastInsertId();

    // 2. Добавяме продуктите (order_items)
    $sql_item = "
        INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase)
        VALUES (:order_id, :product_id, :quantity, :price)
    ";

    foreach ($data['items'] as $item) {
        $stmt = $conn->prepare($sql_item);
        $stmt->execute([
            ':order_id'   => $order_id,
            ':product_id' => $item['product']['product_id'],
            ':quantity'   => $item['quantity'],
            ':price'      => $item['unitPrice']
        ]);

        // 3. Добавяме услугите за всеки продукт (order_services)
        if (!empty($item['services'])) {
            $sql_service = "
                INSERT INTO order_services (order_id, service_id)
                VALUES (:order_id, :service_id)
            ";

            foreach ($item['services'] as $service) {
                $stmt_svc = $conn->prepare($sql_service);
                $stmt_svc->execute([
                    ':order_id'   => $order_id,
                    ':service_id' => $service['service_id']
                ]);
            }
        }
    }

    // 4. Записваме плащането
    $sql_payment = "
        INSERT INTO payments (order_id, amount, payment_method, status, created_at)
        VALUES (:order_id, :amount, :payment_method, :status, NOW())
    ";

    $payment_status = $data['payment_method'] === 'card' ? 'completed' : 'pending';

    $stmt = $conn->prepare($sql_payment);
    $stmt->execute([
        ':order_id'       => $order_id,
        ':amount'         => $total_amount,
        ':payment_method' => $data['payment_method'],
        ':status'         => $payment_status
    ]);

    $conn->commit();

    // Връщаме успех с order_id
    sendResponse(200, [
        'success'  => true,
        'message'  => 'Поръчката е направена успешно!',
        'order_id' => $order_id
    ]);

} catch (PDOException $e) {
    $conn->rollBack();
    sendResponse(500, [
        'success' => false,
        'message' => 'Грешка при записване на поръчката',
        'error'   => DEBUG_MODE ? $e->getMessage() : 'Моля опитайте отново'
    ]);
}
?>