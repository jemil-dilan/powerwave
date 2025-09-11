<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/paypal_config.php';
require_once 'includes/PayPalService.php';

// Log all webhook requests for debugging
error_log('PayPal webhook received: ' . file_get_contents('php://input'));

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

try {
    // Get the webhook body
    $webhookBody = file_get_contents('php://input');
    $webhookData = json_decode($webhookBody, true);
    
    if (!$webhookData) {
        throw new Exception('Invalid webhook data');
    }
    
    // Extract event information
    $eventType = $webhookData['event_type'] ?? '';
    $resource = $webhookData['resource'] ?? [];
    
    error_log("PayPal webhook event: {$eventType}");
    
    // Initialize PayPal service
    $paypalService = new PayPalService();
    
    // In production, you should verify the webhook signature
    // For now, we'll process based on event type
    
    switch ($eventType) {
        case 'PAYMENT.CAPTURE.COMPLETED':
            handlePaymentCaptureCompleted($resource);
            break;
            
        case 'PAYMENT.CAPTURE.DENIED':
            handlePaymentCaptureDenied($resource);
            break;
            
        case 'PAYMENT.CAPTURE.PENDING':
            handlePaymentCapturePending($resource);
            break;
            
        case 'PAYMENT.CAPTURE.REFUNDED':
            handlePaymentCaptureRefunded($resource);
            break;
            
        default:
            error_log("Unhandled PayPal webhook event: {$eventType}");
            break;
    }
    
    // Return 200 OK to acknowledge receipt
    http_response_code(200);
    echo json_encode(['status' => 'received']);
    
} catch (Exception $e) {
    error_log('PayPal webhook error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Handle successful payment capture
 */
function handlePaymentCaptureCompleted($resource) {
    try {
        $db = Database::getInstance();
        $captureId = $resource['id'] ?? '';
        
        if (!$captureId) {
            throw new Exception('No capture ID provided');
        }
        
        // Find the order by PayPal capture ID
        $order = $db->fetchOne(
            "SELECT * FROM orders WHERE payment_transaction_id = ?",
            [$captureId]
        );
        
        if ($order) {
            // Update order status if not already updated
            if ($order['payment_status'] !== 'paid') {
                $db->update('orders', [
                    'payment_status' => 'paid',
                    'status' => 'processing',
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$order['id']]);
                
                // Update PayPal transaction record
                $db->update('paypal_transactions', [
                    'status' => 'captured',
                    'paypal_response' => json_encode($resource),
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'paypal_capture_id = ?', [$captureId]);
                
                error_log("PayPal payment completed for order: {$order['order_number']}");
            }
        } else {
            error_log("Order not found for PayPal capture ID: {$captureId}");
        }
        
    } catch (Exception $e) {
        error_log('Error handling PayPal payment completion: ' . $e->getMessage());
    }
}

/**
 * Handle denied payment capture
 */
function handlePaymentCaptureDenied($resource) {
    try {
        $db = Database::getInstance();
        $captureId = $resource['id'] ?? '';
        
        if (!$captureId) {
            return;
        }
        
        // Find and update the order
        $order = $db->fetchOne(
            "SELECT * FROM orders WHERE payment_transaction_id = ?",
            [$captureId]
        );
        
        if ($order) {
            $db->update('orders', [
                'payment_status' => 'failed',
                'status' => 'cancelled',
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$order['id']]);
            
            // Update PayPal transaction record
            $db->update('paypal_transactions', [
                'status' => 'failed',
                'paypal_response' => json_encode($resource),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'paypal_capture_id = ?', [$captureId]);
            
            error_log("PayPal payment denied for order: {$order['order_number']}");
        }
        
    } catch (Exception $e) {
        error_log('Error handling PayPal payment denial: ' . $e->getMessage());
    }
}

/**
 * Handle pending payment capture
 */
function handlePaymentCapturePending($resource) {
    try {
        $db = Database::getInstance();
        $captureId = $resource['id'] ?? '';
        
        if (!$captureId) {
            return;
        }
        
        // Update PayPal transaction record
        $db->update('paypal_transactions', [
            'status' => 'pending',
            'paypal_response' => json_encode($resource),
            'updated_at' => date('Y-m-d H:i:s')
        ], 'paypal_capture_id = ?', [$captureId]);
        
        error_log("PayPal payment pending for capture ID: {$captureId}");
        
    } catch (Exception $e) {
        error_log('Error handling PayPal payment pending: ' . $e->getMessage());
    }
}

/**
 * Handle refunded payment capture
 */
function handlePaymentCaptureRefunded($resource) {
    try {
        $db = Database::getInstance();
        $captureId = $resource['id'] ?? '';
        
        if (!$captureId) {
            return;
        }
        
        // Find and update the order
        $order = $db->fetchOne(
            "SELECT * FROM orders WHERE payment_transaction_id = ?",
            [$captureId]
        );
        
        if ($order) {
            $db->update('orders', [
                'payment_status' => 'refunded',
                'status' => 'refunded',
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$order['id']]);
            
            // Update PayPal transaction record
            $db->update('paypal_transactions', [
                'status' => 'refunded',
                'paypal_response' => json_encode($resource),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'paypal_capture_id = ?', [$captureId]);
            
            error_log("PayPal payment refunded for order: {$order['order_number']}");
        }
        
    } catch (Exception $e) {
        error_log('Error handling PayPal payment refund: ' . $e->getMessage());
    }
}
?>
