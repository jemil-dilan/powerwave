<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

// Check if we have session data
if (isset($_SESSION['order_id']) && isset($_SESSION['order_number'])) {
    $orderId = $_SESSION['order_id'];
    $orderNumber = $_SESSION['order_number'];
    
    try {
        $db = Database::getInstance();
        
        // Update order status to cancelled
        $db->update('orders', [
            'payment_status' => 'cancelled',
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$orderId]);
        
        // Clear session data
        unset($_SESSION['paypal_payment_id']);
        unset($_SESSION['order_id']);
        unset($_SESSION['order_number']);
        
        showMessage("PayPal payment was cancelled. Order $orderNumber has been cancelled.", 'error');
        
    } catch (Exception $e) {
        error_log("Error updating cancelled order: " . $e->getMessage());
        showMessage('Payment was cancelled.', 'error');
    }
} else {
    showMessage('PayPal payment was cancelled.', 'error');
}

redirect('cart.php');
?>
