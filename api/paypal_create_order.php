<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/paypal_config.php';
require_once '../includes/PayPalService.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $userId = $_SESSION['user_id'];
    $cartItems = getCartItems($userId);
    
    if (empty($cartItems)) {
        throw new Exception('Cart is empty');
    }
    
    // Calculate totals
    $cartTotal = getCartTotal($userId);
    $shipping = SHIPPING_RATE;
    $tax = round($cartTotal * TAX_RATE, 2);
    $grandTotal = $cartTotal + $shipping + $tax;
    
    // Validate amount matches
    $requestedAmount = (float)($input['amount'] ?? 0);
    if (abs($requestedAmount - $grandTotal) > 0.01) {
        throw new Exception('Amount mismatch');
    }
    
    // Create PayPal service instance
    $paypalService = new PayPalService();
    
    // Create order description
    $itemCount = count($cartItems);
    $description = "Order from " . SITE_NAME . " - {$itemCount} item(s)";
    
    // Create PayPal order
    $result = $paypalService->createOrder(
        $grandTotal,
        PAYPAL_CURRENCY,
        $description,
        'order_' . time()
    );
    
    if ($result['success']) {
        // Store order info in session for later processing
        $_SESSION['paypal_pending_order'] = [
            'paypal_order_id' => $result['order_id'],
            'amount' => $grandTotal,
            'cart_items' => $cartItems,
            'created_at' => time()
        ];
        
        echo json_encode([
            'success' => true,
            'order_id' => $result['order_id'],
            'approval_url' => $result['approval_url']
        ]);
    } else {
        throw new Exception($result['error']);
    }
    
} catch (Exception $e) {
    error_log('PayPal create order API error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>