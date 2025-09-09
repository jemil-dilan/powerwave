<?php
// File: api/paypal_create_order.php
header('Content-Type: application/json');

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/paypal_config.php';
require_once '../includes/PayPalService.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    $amount = floatval($input['amount'] ?? 0);
    $currency = $input['currency'] ?? 'USD';

    if ($amount <= 0) {
        throw new Exception('Invalid amount');
    }

    // Verify cart total matches requested amount
    $userId = $_SESSION['user_id'];
    $cartTotal = getCartTotal($userId);
    $shipping = SHIPPING_RATE;
    $tax = round($cartTotal * TAX_RATE, 2);
    $grandTotal = $cartTotal + $shipping + $tax;

    if (abs($amount - $grandTotal) > 0.01) {
        throw new Exception('Amount mismatch. Please refresh and try again.');
    }

    // Create PayPal order
    $paypalService = new PayPalService();
    $description = "Order from " . SITE_NAME;
    $result = $paypalService->createOrder($amount, $currency, $description);

    if ($result['success']) {
        // Store order info in session for later completion
        $_SESSION['pending_paypal_order'] = [
            'paypal_order_id' => $result['order_id'],
            'amount' => $amount,
            'currency' => $currency,
            'created_at' => time()
        ];

        echo json_encode([
            'success' => true,
            'order_id' => $result['order_id']
        ]);
    } else {
        throw new Exception($result['error'] ?? 'Failed to create PayPal order');
    }

} catch (Exception $e) {
    error_log('PayPal create order error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>