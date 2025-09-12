<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'outboard_sales2');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_URL', 'http://localhost/outboard-website');
define('SITE_NAME', 'WaveMaster Outboards');
define('SITE_EMAIL', 'wavemasteroutboard@gmail.com');
define('ADMIN_EMAIL', 'wavemasteroutboard@gmail.com');

// Upload configuration
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 6242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('REVIEWS_PER_PAGE', 10);

// Security
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('CSRF_TOKEN_EXPIRE', 1800); // 30 minutes in seconds

// Payment settings
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', '$');
}
if (!defined('CURRENCY_CODE')) {
    define('CURRENCY_CODE', 'USD');
}
if (!defined('TAX_RATE')) {
    define('TAX_RATE', 0.08);
}
if (!defined('SHIPPING_RATE')) {
    define('SHIPPING_RATE', 99.99);
}

//// Payment provider placeholders (replace with your real credentials)
//// PayPal
//define('PAYPAL_CLIENT_ID', '{{PAYPAL_CLIENT_ID}}');
//define('PAYPAL_SECRET', '{{PAYPAL_SECRET}}');
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

// Enhanced session security (must be set before session_start())
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
}

// Security headers
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();

    // Regenerate session ID periodically for security
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}
?>