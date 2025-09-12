<?php
// File: includes/paypal_config.php

// PayPal Configuration
define('PAYPAL_ENVIRONMENT', 'sandbox'); // Change to 'production' for live
define('PAYPAL_CLIENT_ID', 'Aex2JDHopP2P8cfcChTUCmVifD1rkn823DVdu5VElRfAwQVhqQDYX5fa5Ovutwr7xAq8Au2-btaYmxuk');
define('PAYPAL_CLIENT_SECRET', 'EL1-uYaFXGrtwzg4RTIJVDoOOZFeMtwY4EMXBbQAAy0XtvpFjMWl0oqb5a7FBK6kfXJjSrImvGocT_GE');
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
