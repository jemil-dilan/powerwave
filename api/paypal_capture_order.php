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

// CSRF Protection
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validateCSRFToken($token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['orderID'])) {
        throw new Exception('Invalid input - orderID required');
    }
    
    $paypalOrderId = $input['orderID'];
    $userId = $_SESSION['user_id'];
    
    // Check if we have pending order data
    if (!isset($_SESSION['paypal_pending_order']) || 
        $_SESSION['paypal_pending_order']['paypal_order_id'] !== $paypalOrderId) {
        throw new Exception('Invalid or expired order session');
    }
    
    $pendingOrder = $_SESSION['paypal_pending_order'];
    $cartItems = $pendingOrder['cart_items'];
    
    // Create PayPal service instance
    $paypalService = new PayPalService();
    
    // Capture the PayPal order
    $result = $paypalService->captureOrder($paypalOrderId);
    
    if ($result['success']) {
        $db = Database::getInstance();
        $db->beginTransaction();
        
        try {
            // Get user and billing info
            $user = getCurrentUser();
            $billingAddress = $user['address'] ? 
                implode(', ', array_filter([
                    $user['first_name'] . ' ' . $user['last_name'],
                    $user['address'],
                    $user['city'] . ', ' . $user['state'] . ' ' . $user['zip_code'],
                    $user['country']
                ])) : 
                'Address not provided';
            
            // Calculate totals
            $cartTotal = 0;
            foreach ($cartItems as $item) {
                $cartTotal += $item['price'] * $item['quantity'];
            }
            $shipping = SHIPPING_RATE;
            $tax = round($cartTotal * TAX_RATE, 2);
            $grandTotal = $cartTotal + $shipping + $tax;
            
            // Create order in database
            $orderNumber = generateOrderNumber();
            $orderId = $db->insert('orders', [
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'total_amount' => $grandTotal,
                'shipping_cost' => $shipping,
                'tax_amount' => $tax,
                'payment_method' => 'paypal',
                'payment_status' => 'paid',
                'status' => 'processing',
                'shipping_address' => $billingAddress,
                'billing_address' => $billingAddress,
                'payment_transaction_id' => $result['capture_id'],
                'payment_details' => json_encode([
                    'paypal_order_id' => $result['order_id'],
                    'paypal_capture_id' => $result['capture_id'],
                    'payer_email' => $result['payer_email'],
                    'payer_name' => $result['payer_name'],
                    'amount_paid' => $result['amount'],
                    'currency' => $result['currency']
                ])
            ]);
            
            // Create PayPal transaction record
            $db->insert('paypal_transactions', [
                'order_id' => $orderId,
                'paypal_order_id' => $result['order_id'],
                'paypal_capture_id' => $result['capture_id'],
                'status' => 'captured',
                'amount' => $result['amount'],
                'currency' => $result['currency'],
                'payer_email' => $result['payer_email'],
                'payer_name' => $result['payer_name'],
                'paypal_response' => json_encode($result['order_data'])
            ]);
            
            // Add order items
            foreach ($cartItems as $item) {
                $db->insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity']
                ]);
            }
            
            // Clear user's cart
            $db->delete('cart', 'user_id = ?', [$userId]);
            
            $db->commit();
            
            // Send confirmation email
            $emailSubject = "Payment Confirmed - Order " . $orderNumber;
            $emailMessage = "
                <h2>Payment Confirmed!</h2>
                <p>Your PayPal payment has been successfully processed.</p>
                <p><strong>Order Number:</strong> {$orderNumber}</p>
                <p><strong>Amount Paid:</strong> " . formatPrice($result['amount']) . "</p>
                <p><strong>Transaction ID:</strong> {$result['capture_id']}</p>
                <p><strong>PayPal Email:</strong> {$result['payer_email']}</p>
                <p>Your order is now being processed and you will receive a shipping notification soon.</p>
                <p>Thank you for your business!</p>
            ";
            
            sendEmail($user['email'], $emailSubject, $emailMessage);
            
            // Clear session data
            unset($_SESSION['paypal_pending_order']);
            
            echo json_encode([
                'success' => true,
                'order_number' => $orderNumber,
                'transaction_id' => $result['capture_id'],
                'amount' => $result['amount'],
                'redirect_url' => "order_success.php?order={$orderNumber}&method=paypal&status=paid"
            ]);
            
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
        
    } else {
        throw new Exception($result['error']);
    }
    
} catch (Exception $e) {
    error_log('PayPal capture order API error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
