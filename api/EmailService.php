<?php
// ============================================
// EmailService.php - Email Service Class
// ============================================

require_once 'email_config.php';

class EmailService {
    
    /**
     * Send email using PHP mail() function
     */
    public static function sendEmail($to, $subject, $htmlBody, $plainTextBody = '') {
        // Check if email functionality is available
        if (!function_exists('mail')) {
            error_log('Email Error: mail() function not available');
            return false;
        }
        
        // If plain text not provided, strip HTML
        if (empty($plainTextBody)) {
            $plainTextBody = strip_tags($htmlBody);
        }
        
        // Email headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . EMAIL_FROM_NAME . ' <' . EMAIL_FROM_ADDRESS . '>',
            'Reply-To: ' . COMPANY_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $headersString = implode("\r\n", $headers);
        
        // Send email
        $success = @mail($to, $subject, $htmlBody, $headersString);
        
        if (!$success) {
            error_log("Email Error: Failed to send email to {$to}");
        }
        
        return $success;
    }
    
    /**
     * Send welcome email after registration
     */
    public static function sendWelcomeEmail($userEmail, $userName) {
        if (!EMAIL_WELCOME_ENABLED) {
            return false;
        }
        
        $subject = 'Добре дошли в ' . COMPANY_NAME . '!';
        
        $htmlBody = self::getWelcomeEmailTemplate($userName);
        
        return self::sendEmail($userEmail, $subject, $htmlBody);
    }
    
    /**
     * Send order confirmation email
     */
    public static function sendOrderConfirmationEmail($userEmail, $userName, $orderData) {
        if (!EMAIL_ORDER_CONFIRMATION_ENABLED) {
            return false;
        }
        
        $subject = 'Потвърждение на поръчка #' . str_pad($orderData['order_id'], 4, '0', STR_PAD_LEFT);
        
        $htmlBody = self::getOrderConfirmationTemplate($userName, $orderData);
        
        return self::sendEmail($userEmail, $subject, $htmlBody);
    }
    
    /**
     * Send order status update email
     */
    public static function sendOrderStatusUpdateEmail($userEmail, $userName, $orderData, $newStatus) {
        if (!EMAIL_ORDER_STATUS_UPDATE_ENABLED) {
            return false;
        }
        
        $statusLabels = [
            'pending' => 'Чакаща',
            'confirmed' => 'Потвърдена',
            'processing' => 'В обработка',
            'shipped' => 'Изпратена',
            'delivered' => 'Доставена',
            'cancelled' => 'Отказана'
        ];
        
        $statusLabel = $statusLabels[$newStatus] ?? $newStatus;
        
        $subject = 'Промяна в статуса на поръчка #' . str_pad($orderData['order_id'], 4, '0', STR_PAD_LEFT);
        
        $htmlBody = self::getOrderStatusUpdateTemplate($userName, $orderData, $newStatus, $statusLabel);
        
        return self::sendEmail($userEmail, $subject, $htmlBody);
    }
    
    /**
     * Get welcome email HTML template
     */
    private static function getWelcomeEmailTemplate($userName) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8fafc; padding: 30px; }
        .button { display: inline-block; background: #0891b2; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; }
        .footer { background: #333; color: white; padding: 20px; text-align: center; font-size: 14px; border-radius: 0 0 10px 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Добре дошли в " . COMPANY_NAME . "!</h1>
        </div>
        <div class="content">
            <h2>Здравейте, {$userName}!</h2>
            <p>Благодарим Ви, че се регистрирахте в нашия онлайн магазин за климатици!</p>
            <p>Сега можете да:</p>
            <ul>
                <li>✅ Разглеждате нашия каталог с климатици</li>
                <li>✅ Правите бързи поръчки</li>
                <li>✅ Следите статуса на вашите поръчки</li>
                <li>✅ Управлявате вашия профил</li>
            </ul>
            <p style="text-align: center;">
                <a href="" . COMPANY_WEBSITE . "/products.html" class="button">Разгледай каталога</a>
            </p>
            <p>Ако имате въпроси, не се колебайте да се свържете с нас на <a href="mailto:" . COMPANY_EMAIL . "\">" . COMPANY_EMAIL . "</a></p>
        </div>
        <div class="footer">
            <p><strong>" . COMPANY_NAME . "</strong></p>
            <p>" . COMPANY_ADDRESS . "</p>
            <p>Телефон: " . COMPANY_PHONE . " | Email: " . COMPANY_EMAIL . "</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get order confirmation email HTML template
     */
    private static function getOrderConfirmationTemplate($userName, $orderData) {
        $orderNumber = str_pad($orderData['order_id'], 4, '0', STR_PAD_LEFT);
        $totalAmount = number_format($orderData['total_amount'], 2, '.', '');
        
        // Build products list
        $productsHTML = '';
        if (isset($orderData['items'])) {
            foreach ($orderData['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $productsHTML .= "
                    <tr>
                        <td>{$item['model_name']}</td>
                        <td style='text-align: center;'>{$item['quantity']}</td>
                        <td style='text-align: right;'>" . number_format($item['unit_price'], 2) . " лв</td>
                        <td style='text-align: right;'><strong>" . number_format($itemTotal, 2) . " лв</strong></td>
                    </tr>
                ";
            }
        }
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8fafc; padding: 30px; }
        .order-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0891b2; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f8fafc; font-weight: 600; }
        .total-row { font-size: 1.2em; font-weight: bold; background: #f0f9ff; }
        .button { display: inline-block; background: #0891b2; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; }
        .footer { background: #333; color: white; padding: 20px; text-align: center; font-size: 14px; border-radius: 0 0 10px 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Поръчката е потвърдена!</h1>
            <p style="font-size: 1.2em; margin: 10px 0;">Поръчка #{$orderNumber}</p>
        </div>
        <div class="content">
            <h2>Здравейте, {$userName}!</h2>
            <p>Благодарим Ви за поръчката! Вашата поръчка е приета и ще бъде обработена в най-кратък срок.</p>
            
            <div class="order-box">
                <h3>📦 Детайли на поръчката</h3>
                <p><strong>Номер:</strong> #{$orderNumber}</p>
                <p><strong>Дата:</strong> " . date('d.m.Y H:i') . "</p>
                <p><strong>Адрес за доставка:</strong> {$orderData['delivery_address']}</p>
            </div>
            
            <h3>🛒 Поръчани продукти:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Продукт</th>
                        <th style='text-align: center;'>Количество</th>
                        <th style='text-align: right;'>Цена</th>
                        <th style='text-align: right;'>Общо</th>
                    </tr>
                </thead>
                <tbody>
                    {$productsHTML}
                    <tr class='total-row'>
                        <td colspan='3'>Обща сума:</td>
                        <td style='text-align: right;'>{$totalAmount} лв</td>
                    </tr>
                </tbody>
            </table>
            
            <p style="text-align: center;">
                <a href=\"" . COMPANY_WEBSITE . "/user-profile.html\" class=\"button\">Виж поръчката</a>
            </p>
            
            <p><strong>Очаквайте потвърждение от нашия екип в рамките на 24 часа.</strong></p>
            <p>При въпроси се свържете с нас на <a href=\"mailto:" . COMPANY_EMAIL . "\">" . COMPANY_EMAIL . "</a></p>
        </div>
        <div class="footer">
            <p><strong>" . COMPANY_NAME . "</strong></p>
            <p>" . COMPANY_ADDRESS . "</p>
            <p>Телефон: " . COMPANY_PHONE . " | Email: " . COMPANY_EMAIL . "</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get order status update email HTML template
     */
    private static function getOrderStatusUpdateTemplate($userName, $orderData, $newStatus, $statusLabel) {
        $orderNumber = str_pad($orderData['order_id'], 4, '0', STR_PAD_LEFT);
        
        $statusMessages = [
            'confirmed' => '✅ Вашата поръчка е потвърдена и ще бъде обработена.',
            'processing' => '⚙️ Вашата поръчка се обработва в момента.',
            'shipped' => '🚚 Вашата поръчка е изпратена и е на път към вас!',
            'delivered' => '🎉 Вашата поръчка е доставена успешно!',
            'cancelled' => '❌ Вашата поръчка е отказана.'
        ];
        
        $message = $statusMessages[$newStatus] ?? 'Статусът на вашата поръчка е променен.';
        
        $statusColor = [
            'pending' => '#f59e0b',
            'confirmed' => '#0891b2',
            'processing' => '#3b82f6',
            'shipped' => '#8b5cf6',
            'delivered' => '#10b981',
            'cancelled' => '#ef4444'
        ];
        
        $color = $statusColor[$newStatus] ?? '#0891b2';
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, {$color} 0%, {$color} 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8fafc; padding: 30px; }
        .status-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid {$color}; text-align: center; }
        .status-label { font-size: 1.5em; font-weight: bold; color: {$color}; }
        .button { display: inline-block; background: #0891b2; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; }
        .footer { background: #333; color: white; padding: 20px; text-align: center; font-size: 14px; border-radius: 0 0 10px 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Промяна в статуса на поръчка</h1>
            <p style="font-size: 1.2em; margin: 10px 0;">Поръчка #{$orderNumber}</p>
        </div>
        <div class="content">
            <h2>Здравейте, {$userName}!</h2>
            <p>{$message}</p>
            
            <div class="status-box">
                <p style="margin-bottom: 10px; color: #666;">Нов статус:</p>
                <div class="status-label">{$statusLabel}</div>
            </div>
            
            <p style="text-align: center;">
                <a href=\"" . COMPANY_WEBSITE . "/user-profile.html\" class=\"button\">Виж поръчката</a>
            </p>
            
            <p>При въпроси се свържете с нас на <a href=\"mailto:" . COMPANY_EMAIL . "\">" . COMPANY_EMAIL . "</a></p>
        </div>
        <div class="footer">
            <p><strong>" . COMPANY_NAME . "</strong></p>
            <p>" . COMPANY_ADDRESS . "</p>
            <p>Телефон: " . COMPANY_PHONE . " | Email: " . COMPANY_EMAIL . "</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
?>