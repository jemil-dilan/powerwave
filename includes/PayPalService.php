<?php

/**
 * PayPal Service Class
 * Modern implementation using PayPal Orders API v2
 */
class PayPalService {
    private $clientId;
    private $clientSecret;
    private $baseUrl;
    private $environment;
    
    public function __construct() {
        $this->environment = PAYPAL_ENVIRONMENT;
        $this->clientId = PAYPAL_CLIENT_ID;
        $this->clientSecret = PAYPAL_CLIENT_SECRET;
        $this->baseUrl = PAYPAL_BASE_URL;
    }
    
    /**
     * Get PayPal Access Token
     */
    private function getAccessToken() {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/v1/oauth2/token',
            CURLOPT_HEADER => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->clientId . ':' . $this->clientSecret,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Accept-Language: en_US',
            ]
        ]);\n        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        }
        
        error_log('PayPal access token error: ' . $response);
        return null;
    }
    
    /**
     * Create PayPal Order using Orders API v2
     */
    public function createOrder($amount, $currency = 'USD', $description = 'Outboard Motor Purchase', $orderId = null) {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return ['success' => false, 'error' => 'Failed to get PayPal access token'];
        }
        
        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $orderId ?? 'default',
                    'description' => $description,
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($amount, 2, '.', '')
                    ]
                ]
            ],
            'application_context' => [
                'brand_name' => SITE_NAME,
                'landing_page' => 'BILLING',
                'user_action' => 'PAY_NOW',
                'return_url' => PAYPAL_SUCCESS_URL,
                'cancel_url' => PAYPAL_CANCEL_URL
            ]
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/v2/checkout/orders',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($orderData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
                'PayPal-Request-Id: ' . uniqid(), // Idempotency key
                'Prefer: return=representation'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 201 && $response) {
            $order = json_decode($response, true);
            
            // Find approval URL
            foreach ($order['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return [
                        'success' => true,
                        'order_id' => $order['id'],
                        'approval_url' => $link['href'],
                        'order_data' => $order
                    ];
                }
            }
        }
        
        error_log('PayPal create order error: ' . $response);
        return ['success' => false, 'error' => 'Failed to create PayPal order', 'response' => $response];
    }
    
    /**
     * Capture PayPal Order
     */
    public function captureOrder($orderId) {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return ['success' => false, 'error' => 'Failed to get PayPal access token'];
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/v2/checkout/orders/' . $orderId . '/capture',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => '{}',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
                'PayPal-Request-Id: ' . uniqid(),
                'Prefer: return=representation'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 201 && $response) {
            $order = json_decode($response, true);
            
            if ($order['status'] === 'COMPLETED') {
                $capture = $order['purchase_units'][0]['payments']['captures'][0];
                
                return [
                    'success' => true,
                    'order_id' => $order['id'],
                    'capture_id' => $capture['id'],
                    'amount' => $capture['amount']['value'],
                    'currency' => $capture['amount']['currency_code'],
                    'payer_email' => $order['payer']['email_address'] ?? '',
                    'payer_name' => ($order['payer']['name']['given_name'] ?? '') . ' ' . ($order['payer']['name']['surname'] ?? ''),
                    'order_data' => $order
                ];
            }
        }
        
        error_log('PayPal capture order error: ' . $response);
        return ['success' => false, 'error' => 'Failed to capture PayPal order', 'response' => $response];
    }
    
    /**
     * Get Order Details
     */
    public function getOrderDetails($orderId) {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return ['success' => false, 'error' => 'Failed to get PayPal access token'];
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/v2/checkout/orders/' . $orderId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $order = json_decode($response, true);
            return ['success' => true, 'order' => $order];
        }
        
        return ['success' => false, 'error' => 'Failed to get order details', 'response' => $response];
    }
    
    /**
     * Verify webhook signature (for production use)
     */
    public function verifyWebhookSignature($headers, $body, $webhookId) {
        // This would contain webhook verification logic
        // For now, return true for development
        return true;
    }
    
    /**
     * Process webhook event
     */
    public function processWebhook($eventType, $resource) {
        switch ($eventType) {
            case 'PAYMENT.CAPTURE.COMPLETED':
                return $this->handlePaymentCompleted($resource);
            case 'PAYMENT.CAPTURE.DENIED':
                return $this->handlePaymentDenied($resource);
            default:
                error_log('Unhandled PayPal webhook event: ' . $eventType);
                return false;
        }
    }
    
    private function handlePaymentCompleted($resource) {
        // Handle successful payment
        error_log('PayPal payment completed: ' . json_encode($resource));
        return true;
    }
    
    private function handlePaymentDenied($resource) {
        // Handle denied payment
        error_log('PayPal payment denied: ' . json_encode($resource));
        return true;
    }
}

/**
 * Helper function to get PayPal SDK URL with parameters
 */
function getPayPalSDKUrl($currency = 'USD', $intent = 'capture') {
    $params = [
        'client-id' => PAYPAL_CLIENT_ID,
        'currency' => $currency,
        'intent' => $intent,
        'components' => 'buttons,funding-eligibility'
    ];
    
    return PAYPAL_JS_SDK_URL . '?' . http_build_query($params);
}

?>
