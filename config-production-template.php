<?php
// PRODUCTION CONFIGURATION TEMPLATE
// Copy this to includes/config.php and update with your production values

// Database configuration - UPDATE THESE!
define('DB_HOST', 'localhost'); // Your production database host
define('DB_NAME', 'your_production_database_name');
define('DB_USER', 'your_production_database_user');
define('DB_PASS', 'your_secure_production_password');

// Site configuration - UPDATE THESE!
define('SITE_URL', 'https://yourdomain.com'); // Your production URL
define('SITE_NAME', 'WaveMaster Outboards');
define('SITE_EMAIL', 'noreply@yourdomain.com'); // Your production email
define('ADMIN_EMAIL', 'admin@yourdomain.com'); // Admin email

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
define('CURRENCY_SYMBOL', '$');
define('CURRENCY_CODE', 'USD');
define('TAX_RATE', 0.08); // 8% tax rate
define('SHIPPING_RATE', 99.99); // Flat shipping rate

// Apple Pay
define('APPLE_PAY_MERCHANT_ID', 'merchant.yourdomain.com');
// Cash App
define('CASH_APP_CASHTAG', '$YourCashTag');
// Bank Account (display-only details)
define('BANK_ACCOUNT_NAME', 'Your Business Name');
define('BANK_ACCOUNT_NUMBER', '****-****-1234'); // Masked for security
define('BANK_ROUTING_NUMBER', '****5678'); // Masked for security

// Email settings - UPDATE THESE!
define('SMTP_HOST', 'smtp.yourmailprovider.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-smtp-username');
define('SMTP_PASSWORD', 'your-smtp-password');
define('SMTP_SECURE', 'tls');

// ⚠️ CRITICAL: Production security settings
define('DEBUG', false); // MUST be false in production
error_reporting(0); // Disable error display
ini_set('display_errors', 0); // Hide errors from users
ini_set('log_errors', 1); // Log errors to file
ini_set('error_log', 'error.log'); // Error log file

// Timezone
date_default_timezone_set('America/New_York'); // Update to your timezone

// Enhanced session security
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1); // Force HTTPS
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
    
    // Force HTTPS in production
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Start session
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