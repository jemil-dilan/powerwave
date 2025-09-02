<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/paypal_config.php';

requireLogin();

// Check if we have the required parameters
if (!isset($_GET['paymentId']) || !isset($_GET['PayerID'])) {
    showMessage('Invalid PayPal response. Please try again.', 'error');
    redirect('cart.php');
}

// Check if we have session data
if (!isset($_SESSION['paypal_payment_id']) || !isset($_SESSION['order_id'])) {
    showMessage('Session expired. Please restart your order.', 'error');
    redirect('cart.php');
}

$paymentId = $_GET['paymentId'];
$payerId = $_GET['PayerID'];
$sessionPaymentId = $_SESSION['paypal_payment_id'];
$orderId = $_SESSION['order_id'];
$orderNumber = $_SESSION['order_number'] ?? '';

// Verify payment ID matches
if ($paymentId !== $sessionPaymentId) {
    showMessage('Payment verification failed. Please contact support.', 'error');
    redirect('cart.php');
}

try {
    $db = Database::getInstance();
    
    // Execute PayPal payment
    $result = executePayPalPayment($paymentId, $payerId);
    
    if ($result['success']) {
        // Payment successful - update order
        $updateData = [
            'payment_status' => 'completed',
            'status' => 'processing',
            'payment_transaction_id' => $result['transaction_id'],
            'payment_details' => json_encode([
                'paypal_payment_id' => $result['payment_id'],
                'paypal_transaction_id' => $result['transaction_id'],
                'payer_email' => $result['payer_email'],
                'amount_paid' => $result['amount']
            ]),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $db->update('orders', $updateData, 'id = ?', [$orderId]);
        
        // Get order details for email
        $order = $db->fetchOne('SELECT * FROM orders WHERE id = ?', [$orderId]);
        $user = getCurrentUser();
        
        // Send payment confirmation email
        $emailSubject = "Payment Confirmed - Order $orderNumber";
        $emailMessage = "
            <h2>Payment Confirmed!</h2>
            <p>Your PayPal payment has been successfully processed.</p>
            <p><strong>Order Number:</strong> $orderNumber</p>
            <p><strong>Amount Paid:</strong> " . formatPrice($result['amount']) . "</p>
            <p><strong>Transaction ID:</strong> {$result['transaction_id']}</p>
            <p><strong>PayPal Email:</strong> {$result['payer_email']}</p>
            <p>Your order is now being processed and you will receive a shipping notification soon.</p>
            <p>Thank you for your business!</p>
        ";
        
        sendEmail($user['email'], $emailSubject, $emailMessage);
        
        // Clear session data
        unset($_SESSION['paypal_payment_id']);
        unset($_SESSION['order_id']);
        unset($_SESSION['order_number']);
        
        // Redirect to success page
        showMessage('Payment successful! Your order has been confirmed.', 'success');
        redirect("order_success.php?order=$orderNumber&method=paypal&status=paid");
        
    } else {
        // Payment execution failed
        $db->update('orders', 
            ['payment_status' => 'failed', 'status' => 'cancelled'], 
            'id = ?', 
            [$orderId]
        );
        
        showMessage('PayPal payment execution failed: ' . $result['error'], 'error');
        redirect('checkout.php');
    }
    
} catch (Exception $e) {
    error_log("PayPal payment execution error: " . $e->getMessage());
    
    // Update order to failed status
    try {
        $db->update('orders', 
            ['payment_status' => 'failed', 'status' => 'cancelled'], 
            'id = ?', 
            [$orderId]
        );
    } catch (Exception $updateError) {
        error_log("Failed to update order status: " . $updateError->getMessage());
    }
    
    showMessage('An error occurred processing your payment. Please contact support.', 'error');
    redirect('cart.php');
}
?>
