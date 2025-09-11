<?php
// File: api/paypal_capture_order.php
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

// CSRF Protection
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validateCSRFToken($token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $orderID = $input['orderID'] ?? '';

    if (empty($orderID)) {
        throw new Exception('No order ID provided');
    }

    // Verify this order belongs to current session
    if (!isset($_SESSION['pending_paypal_order']) ||
        $_SESSION['pending_paypal_order']['paypal_order_id'] !== $orderID) {
        throw new Exception('Invalid order ID for current session');
    }

    // Capture the payment
    $paypalService = new PayPalService();
    $result = $paypalService->captureOrder($orderID);

    if ($result['success']) {
        $userId = $_SESSION['user_id'];
        $cartItems = getCartItems($userId);

        if (empty($cartItems)) {
            throw new Exception('Cart is empty');
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // Calculate totals
            $cartTotal = getCartTotal($userId);
            $shipping = SHIPPING_RATE;
            $tax = round($cartTotal * TAX_RATE, 2);
            $grandTotal = $cartTotal + $shipping + $tax;

            // Get user info for billing address
            $user = getCurrentUser();
            $billingAddress = implode(', ', [
                $user['first_name'] . ' ' . $user['last_name'],
                $user['address'] ?? '',
                $user['city'] ?? '',
                $user['state'] ?? '',
                $user['zip_code'] ?? '',
                $user['country'] ?? ''
            ]);

            // Create order
            $orderNumber = generateOrderNumber();
            $orderId = $db->insert('orders', [
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'total_amount' => $grandTotal,
                'shipping_cost' => $shipping,
                'tax_amount' => $tax,
                'payment_method' => 'paypal',
                'payment_transaction_id' => $result['capture_id'],
                'payment_details' => json_encode($result['order_data']),
                'shipping_address' => $billingAddress,
                'billing_address' => $billingAddress,
                'status' => 'processing',
                'payment_status' => 'paid'
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

            // Create PayPal transaction record
            $db->insert('paypal_transactions', [
                'order_id' => $orderId,
                'paypal_order_id' => $orderID,
                'paypal_capture_id' => $result['capture_id'],
                'status' => 'captured',
                'amount' => $result['amount'],
                'currency' => $result['currency'],
                'payer_email' => $result['payer_email'],
                'payer_name' => $result['payer_name'],
                'paypal_response' => json_encode($result['order_data'])
            ]);

            // Clear cart
            $db->delete('cart', 'user_id = ?', [$userId]);

            $db->commit();

            // Clear session data
            unset($_SESSION['pending_paypal_order']);

            // Send confirmation email
            $emailSubject = "Order Confirmation - $orderNumber";
            $emailMessage = "
                <h2>Thank you for your order!</h2>
                <p>Order Number: <strong>$orderNumber</strong></p>
                <p>Total: <strong>" . formatPrice($grandTotal) . "</strong></p>
                <p>Payment Method: <strong>PayPal</strong></p>
                <p>Your payment has been processed successfully.</p>
            ";

            sendEmail($user['email'], $emailSubject, $emailMessage);

            echo json_encode([
                'success' => true,
                'order_number' => $orderNumber,
                'redirect_url' => SITE_URL . '/order_success.php?order=' . $orderNumber . '&method=paypal'
            ]);

        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }

    } else {
        throw new Exception($result['error'] ?? 'Failed to capture PayPal payment');
    }

} catch (Exception $e) {
    error_log('PayPal capture order error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>