<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'outboard_sales2');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_URL', 'http://localhost/outboard-website');
define('SITE_NAME', 'PowerWave outboards');
define('SITE_EMAIL', 'PowerWave@outboard.com');
define('ADMIN_EMAIL', 'PowerWave@outboardmotorspro.com');

// Upload configuration
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('REVIEWS_PER_PAGE', 10);

// Security
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('CSRF_TOKEN_EXPIRE', 1800); // 30 minutes in seconds

// Payment settings
define('CURRENCY_SYMBOL', '$');
define('CURRENCY_CODE', 'USD');
define('TAX_RATE', 0.08); // 8% tax rate
define('SHIPPING_RATE', 99.99); // Flat shipping rate

// Payment provider placeholders (replace with your real credentials)
// PayPal
define('PAYPAL_CLIENT_ID', '{{PAYPAL_CLIENT_ID}}');
define('PAYPAL_SECRET', '{{PAYPAL_SECRET}}');
// Apple Pay
define('APPLE_PAY_MERCHANT_ID', '{{APPLE_PAY_MERCHANT_ID}}');
// Cash App
define('CASH_APP_CASHTAG', '{{CASH_APP_CASHTAG}}');
// Bank Account (display-only details)
define('BANK_ACCOUNT_NAME', 'Your Business Name');
define('BANK_ACCOUNT_NUMBER', '0000000000');
define('BANK_ROUTING_NUMBER', '000000000');

// Email settings (configure these for production)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_SECURE', 'tls');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

// Timezone
date_default_timezone_set('America/New_York');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
