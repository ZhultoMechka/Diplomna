<?php
// ============================================
// email_config.php - Email Configuration
// ============================================

// Email settings
define('SMTP_ENABLED', false); // Set to true if using SMTP, false for PHP mail()

// SMTP Settings (if SMTP_ENABLED = true)
define('SMTP_HOST', 'smtp.gmail.com'); // e.g., smtp.gmail.com
define('SMTP_PORT', 587); // 587 for TLS, 465 for SSL
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password'); // Use app password for Gmail
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'

// Default sender
define('EMAIL_FROM_ADDRESS', 'noreply@klimatici.com');
define('EMAIL_FROM_NAME', 'Климатици ЕООД');

// Company info (for email templates)
define('COMPANY_NAME', 'Климатици ЕООД');
define('COMPANY_PHONE', '+359 2 XXX XXXX');
define('COMPANY_EMAIL', 'info@klimatici.com');
define('COMPANY_ADDRESS', 'гр. София, ул. Примерна 1');
define('COMPANY_WEBSITE', 'http://localhost/Diplomna');

// Email toggles (enable/disable specific emails)
define('EMAIL_WELCOME_ENABLED', true);
define('EMAIL_ORDER_CONFIRMATION_ENABLED', true);
define('EMAIL_ORDER_STATUS_UPDATE_ENABLED', true);

?>