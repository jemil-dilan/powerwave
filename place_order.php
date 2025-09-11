<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/paypal_config.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('checkout.php');
}

// CSRF Protection
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    showMessage('Invalid security token. Please try again.', 'error');
    redirect('checkout.php');
}

$userId = $_SESSION['user_id'];
$cartItems = getCartItems($userId);

if (empty($cartItems)) {
    showMessage('Your cart is empty.', 'error');
    redirect('cart.php');
}

try {
    $db = Database::getInstance();
    
    // Calculate totals
    $cartTotal = getCartTotal($userId);
    $shipping = SHIPPING_RATE;
    $tax = round($cartTotal * TAX_RATE, 2);
    $grandTotal = $cartTotal + $shipping + $tax;
    
    // Build addresses
    $billingAddress = implode(', ', [
        sanitizeInput($_POST['billing_first_name']) . ' ' . sanitizeInput($_POST['billing_last_name']),
        sanitizeInput($_POST['billing_address']),
        sanitizeInput($_POST['billing_city']) . ', ' . sanitizeInput($_POST['billing_state']) . ' ' . sanitizeInput($_POST['billing_zip']),
        sanitizeInput($_POST['billing_country'])
    ]);
    
    // Use billing as shipping for simplicity
    $shippingAddress = $billingAddress;
    
    $paymentMethod = sanitizeInput($_POST['payment_method']);
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    // Start transaction
    $db->beginTransaction();
    
    // Create order
    $orderNumber = generateOrderNumber();
    $orderId = $db->insert('orders', [
        'user_id' => $userId,
        'order_number' => $orderNumber,
        'total_amount' => $grandTotal,
        'shipping_cost' => $shipping,
        'tax_amount' => $tax,
        'payment_method' => $paymentMethod,
        'shipping_address' => $shippingAddress,
        'billing_address' => $billingAddress,
        'notes' => $notes,
        'status' => 'pending',
        'payment_status' => 'pending'
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
    
    // Clear cart
    $db->delete('cart', 'user_id = ?', [$userId]);
    
    $db->commit();
    
    // Handle different payment methods
    if ($paymentMethod === 'paypal') {
        // For PayPal, we use the JavaScript SDK integration
        // The actual payment processing happens via AJAX in checkout.php
        // This fallback is for non-JavaScript users
        
        require_once 'includes/PayPalService.php';
        $paypalService = new PayPalService();
        
        $description = "Order $orderNumber - " . SITE_NAME;
        $paypalResult = $paypalService->createOrder($grandTotal, PAYPAL_CURRENCY, $description, $orderNumber);
        
        if ($paypalResult['success']) {
            // Store payment info for later completion
            $_SESSION['paypal_payment_id'] = $paypalResult['order_id'];
            $_SESSION['order_id'] = $orderId;
            $_SESSION['order_number'] = $orderNumber;
            
            // Redirect to PayPal for approval
            header('Location: ' . $paypalResult['approval_url']);
            exit;
        } else {
            // PayPal payment failed
            $db->update('orders', ['payment_status' => 'failed', 'status' => 'cancelled'], 'id = ?', [$orderId]);
            showMessage('PayPal payment setup failed. Please try again or use a different payment method.', 'error');
            redirect('checkout.php');
        }
    } elseif ($paymentMethod === 'crypto') {
        // Coinbase Commerce integration
        require_once 'includes/crypto_config.php';

        $chargeData = [
            'name' => SITE_NAME . ' Order #' . $orderNumber,
            'description' => 'Payment for Order #' . $orderNumber,
            'local_price' => [
                'amount' => $grandTotal,
                'currency' => 'USD'
            ],
            'pricing_type' => 'fixed_price',
            'metadata' => [
                'order_id' => $orderId,
                'user_id' => $userId,
                'order_number' => $orderNumber
            ],
            'redirect_url' => SITE_URL . '/order_success.php?order=' . $orderNumber,
            'cancel_url' => SITE_URL . '/checkout.php?payment_cancelled=1'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, COINBASE_COMMERCE_API_URL . '/charges');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($chargeData));

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-CC-Api-Key: ' . COINBASE_COMMERCE_API_KEY,
            'X-CC-Version: 2018-03-22'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            $charge = json_decode($response, true)['data'];

            // Log the charge to the new table
            $db->insert('coinbase_charges', [
                'order_id' => $orderId,
                'charge_code' => $charge['code'],
                'status' => $charge['timeline'][0]['status'], // e.g., 'NEW'
                'amount' => $charge['pricing']['local']['amount'],
                'currency' => $charge['pricing']['local']['currency'],
                'hosted_url' => $charge['hosted_url'],
                'created_at' => date('Y-m-d H:i:s', strtotime($charge['created_at'])),
                'expires_at' => date('Y-m-d H:i:s', strtotime($charge['expires_at']))
            ]);

            // Redirect user to the Coinbase Commerce payment page
            header('Location: ' . $charge['hosted_url']);
            exit;
        } else {
            // Handle API error
            error_log('Coinbase Commerce API Error: ' . $response);
            $db->update('orders', ['payment_status' => 'failed', 'status' => 'cancelled'], 'id = ?', [$orderId]);
            showMessage('Could not connect to cryptocurrency payment gateway. Please try another method or contact support.', 'error');
            redirect('checkout.php');
        }

    } else {
        // For other payment methods (e.g., bank transfer), send confirmation email
        $user = getCurrentUser();
        $emailSubject = "Order Confirmation - $orderNumber";
        $emailMessage = "
            <h2>Thank you for your order!</h2>
            <p>Order Number: <strong>$orderNumber</strong></p>
            <p>Total: <strong>" . formatPrice($grandTotal) . "</strong></p>
            <p>Payment Method: <strong>" . ucfirst($paymentMethod) . "</strong></p>
            <p>We will process your order shortly and send you payment instructions.</p>
        ";
        
        sendEmail($user['email'], $emailSubject, $emailMessage);
        
        // Redirect to order success page
        redirect("order_success.php?order=$orderNumber&method=$paymentMethod");
    }
    
} catch (Exception $e) {
    // Only rollback if transaction is active
    if ($db && $db->getConnection()->inTransaction()) {
        $db->rollback();
    }
    error_log("Order creation failed: " . $e->getMessage());
    showMessage('There was an error processing your order. Please try again.', 'error');
    redirect('checkout.php');
}
?>
