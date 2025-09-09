<?php
// File: includes/paypal_config.php

// PayPal Configuration
define('PAYPAL_ENVIRONMENT', 'sandbox'); // Change to 'production' for live
define('PAYPAL_CLIENT_ID', 'YOUR_PAYPAL_CLIENT_ID_HERE');
define('PAYPAL_CLIENT_SECRET', 'YOUR_PAYPAL_CLIENT_SECRET_HERE');
define('PAYPAL_CURRENCY', 'USD');

// PayPal API URLs
if (PAYPAL_ENVIRONMENT === 'production') {
    define('PAYPAL_BASE_URL', 'https://api.paypal.com');
    define('PAYPAL_JS_SDK_URL', 'https://www.paypal.com/sdk/js');
} else {
    define('PAYPAL_BASE_URL', 'https://api.sandbox.paypal.com');
    define('PAYPAL_JS_SDK_URL', 'https://www.paypal.com/sdk/js');
}

// Return URLs
define('PAYPAL_SUCCESS_URL', SITE_URL . '/payment_success.php');
define('PAYPAL_CANCEL_URL', SITE_URL . '/checkout.php?payment_cancelled=1');

// Webhook URL (for production)
define('PAYPAL_WEBHOOK_URL', SITE_URL . '/paypal_webhook.php');

?>
