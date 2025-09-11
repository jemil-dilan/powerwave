<?php
require_once 'database.php';
/**
 * PayPal Configuration for Ubuntu Deployment
 * 
 * UBUNTU DEPLOYMENT INSTRUCTIONS:
 * 1. Go to https://developer.paypal.com
 * 2. Log in and go to "My Apps & Credentials"
 * 3. Create a new app or use existing one
 * 4. Copy your Client ID and Client Secret
 * 5. Edit this file on Ubuntu: nano includes/paypal_config.php
 * 6. Replace YOUR_SANDBOX_CLIENT_ID_HERE and YOUR_SANDBOX_CLIENT_SECRET_HERE
 * 7. For production, change PAYPAL_ENVIRONMENT to 'production'
 */

// PayPal Environment - 'sandbox' for testing, 'production' for live
define('PAYPAL_ENVIRONMENT', 'sandbox');

// PayPal API Credentials
// REPLACE THESE WITH YOUR ACTUAL CREDENTIALS FROM https://developer.paypal.com
if (PAYPAL_ENVIRONMENT === 'sandbox') {
    // Sandbox credentials - REPLACE WITH YOUR SANDBOX CREDENTIALS
    if (!defined('PAYPAL_CLIENT_ID')) {
        define('PAYPAL_CLIENT_ID', 'YOUR_SANDBOX_CLIENT_ID_HERE');
    }
    if (!defined('PAYPAL_CLIENT_SECRET')) {
        define('PAYPAL_CLIENT_SECRET', 'YOUR_SANDBOX_CLIENT_SECRET_HERE');
    }
    if (!defined('PAYPAL_BASE_URL')) {
        define('PAYPAL_BASE_URL', 'https://api-m.sandbox.paypal.com');
    }
    if (!defined('PAYPAL_WEB_URL')) {
        define('PAYPAL_WEB_URL', 'https://www.sandbox.paypal.com');
    }
} else {
    // Production credentials - REPLACE WITH YOUR LIVE CREDENTIALS
    if (!defined('PAYPAL_CLIENT_ID')) {
        define('PAYPAL_CLIENT_ID', 'YOUR_PRODUCTION_CLIENT_ID_HERE');
    }
    if (!defined('PAYPAL_CLIENT_SECRET')) {
        define('PAYPAL_CLIENT_SECRET', 'YOUR_PRODUCTION_CLIENT_SECRET_HERE');
    }
    if (!defined('PAYPAL_BASE_URL')) {
        define('PAYPAL_BASE_URL', 'https://api-m.paypal.com');
    }
    if (!defined('PAYPAL_WEB_URL')) {
        define('PAYPAL_WEB_URL', 'https://www.paypal.com');
    }
}

// PayPal Currency and Settings
define('PAYPAL_CURRENCY', 'USD');
define('PAYPAL_INTENT', 'capture'); // 'capture' for immediate capture, 'authorize' for later capture

// Return URLs for redirect-based flow (if needed)
define('PAYPAL_SUCCESS_URL', SITE_URL . '/paypal_success.php');
define('PAYPAL_CANCEL_URL', SITE_URL . '/paypal_cancel.php');

// PayPal JavaScript SDK URL
define('PAYPAL_JS_SDK_URL', 'https://www.paypal.com/sdk/js');

// PayPal Webhook URL (for production use)
define('PAYPAL_WEBHOOK_URL', SITE_URL . '/paypal_webhook.php');

// Note: getPayPalSDKUrl() function is now defined in PayPalService.php to avoid conflicts

/**
 * Get PayPal Access Token
 */
function getPayPalAccessToken() {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        return isset($data['access_token']) ? $data['access_token'] : null;
    }
    
    return null;
}

/**
 * Create PayPal Payment
 */
function createPayPalPayment($amount, $currency = 'USD', $description = 'Outboard Motor Purchase') {
    $accessToken = getPayPalAccessToken();
    
    if (!$accessToken) {
        return ['success' => false, 'error' => 'Failed to get PayPal access token'];
    }
    
    $paymentData = [
        'intent' => 'sale',
        'payer' => [
            'payment_method' => 'paypal'
        ],
        'transactions' => [
            [
                'amount' => [
                    'total' => number_format($amount, 2, '.', ''),
                    'currency' => $currency
                ],
                'description' => $description
            ]
        ],
        'redirect_urls' => [
            'return_url' => PAYPAL_SUCCESS_URL,
            'cancel_url' => PAYPAL_CANCEL_URL
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . '/v1/payments/payment');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 201 && $response) {
        $payment = json_decode($response, true);
        
        // Find approval URL
        foreach ($payment['links'] as $link) {
            if ($link['rel'] === 'approval_url') {
                return [
                    'success' => true,
                    'payment_id' => $payment['id'],
                    'approval_url' => $link['href']
                ];
            }
        }
    }
    
    return ['success' => false, 'error' => 'Failed to create PayPal payment', 'response' => $response];
}

/**
 * Execute PayPal Payment
 */
function executePayPalPayment($paymentId, $payerId) {
    $accessToken = getPayPalAccessToken();
    
    if (!$accessToken) {
        return ['success' => false, 'error' => 'Failed to get PayPal access token'];
    }
    
    $executeData = [
        'payer_id' => $payerId
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . '/v1/payments/payment/' . $paymentId . '/execute');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($executeData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $payment = json_decode($response, true);
        
        if ($payment['state'] === 'approved') {
            return [
                'success' => true,
                'payment_id' => $payment['id'],
                'payer_email' => $payment['payer']['payer_info']['email'] ?? '',
                'transaction_id' => $payment['transactions'][0]['related_resources'][0]['sale']['id'] ?? '',
                'amount' => $payment['transactions'][0]['amount']['total'] ?? 0
            ];
        }
    }
    
    return ['success' => false, 'error' => 'Failed to execute PayPal payment', 'response' => $response];
}
?>
