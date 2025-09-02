<?php
/**
 * PayPal Configuration - Updated for PayPal JavaScript SDK v2
 * 
 * INSTRUCTIONS:
 * 1. Get your Client ID from PayPal Developer Dashboard
 * 2. Replace PAYPAL_CLIENT_ID_SANDBOX with your sandbox client ID
 * 3. Replace PAYPAL_CLIENT_ID_PRODUCTION with your production client ID
 * 4. Replace PAYPAL_CLIENT_SECRET_* with your actual client secrets
 * 5. Set PAYPAL_ENVIRONMENT to 'production' when ready to go live
 */

// PayPal Environment - 'sandbox' for testing, 'production' for live
define('PAYPAL_ENVIRONMENT', 'sandbox');

// PayPal API Credentials
if (PAYPAL_ENVIRONMENT === 'sandbox') {
    // Sandbox credentials - Replace with your actual sandbox credentials
    define('PAYPAL_CLIENT_ID', 'AQZ8kYKhKKdS8fF8oaKd_NiFUADsqe8bKE5MYKhKKdS8fF8oaKd_NiFUADsqe8bKE5M'); // Placeholder sandbox ID
    define('PAYPAL_CLIENT_SECRET', 'ELKhKKdS8fF8oaKd_NiFUADsqe8bKE5MYKhKKdS8fF8oaKd_NiFUADsqe8bKE5M'); // Placeholder sandbox secret
    define('PAYPAL_BASE_URL', 'https://api.sandbox.paypal.com');
} else {
    // Production credentials - Replace with your actual production credentials
    define('PAYPAL_CLIENT_ID', 'YOUR_PRODUCTION_CLIENT_ID');
    define('PAYPAL_CLIENT_SECRET', 'YOUR_PRODUCTION_CLIENT_SECRET');
    define('PAYPAL_BASE_URL', 'https://api.paypal.com');
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
