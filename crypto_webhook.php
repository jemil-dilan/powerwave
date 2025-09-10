<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/crypto_config.php';

// Get the request body and headers
$payload = file_get_contents('php://input');
$headers = getallheaders();

// Validate the webhook signature
$signatureHeader = $headers['X-Cc-Webhook-Signature'] ?? '';
$computedSignature = hash_hmac('sha256', $payload, COINBASE_COMMERCE_WEBHOOK_SECRET);

if (!hash_equals($computedSignature, $signatureHeader)) {
    http_response_code(400);
    error_log('Invalid Coinbase webhook signature.');
    exit('Invalid signature');
}

// Decode the event
$event = json_decode($payload, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    error_log('Invalid JSON in Coinbase webhook.');
    exit('Invalid JSON');
}

// Handle the event
if (isset($event['event']['type'])) {
    $eventType = $event['event']['type'];
    $chargeData = $event['event']['data'];

    try {
        $db = Database::getInstance();
        $orderId = $chargeData['metadata']['order_id'] ?? null;

        if (!$orderId) {
            error_log('Webhook received without order_id in metadata.');
            http_response_code(400);
            exit('Missing order_id');
        }

        $chargeCode = $chargeData['code'] ?? '';

        switch ($eventType) {
            case 'charge:confirmed':
                // Payment was successful
                $db->update('orders', 
                    ['payment_status' => 'paid', 'status' => 'processing'], 
                    'id = ?', 
                    [$orderId]
                );
                $db->update('coinbase_charges', ['status' => 'CONFIRMED'], 'charge_code = ?', [$chargeCode]);
                error_log("Order $orderId successfully updated to PAID/PROCESSING via Coinbase webhook.");
                break;

            case 'charge:failed':
                // Payment failed
                $db->update('orders', 
                    ['payment_status' => 'failed', 'status' => 'cancelled'], 
                    'id = ?', 
                    [$orderId]
                );
                $db->update('coinbase_charges', ['status' => 'FAILED'], 'charge_code = ?', [$chargeCode]);
                error_log("Order $orderId marked as FAILED/CANCELLED via Coinbase webhook.");
                break;

            // You can add other cases like charge:pending, charge:delayed, etc.
            default:
                // Unhandled event type
                error_log("Unhandled Coinbase event type: $eventType");
                break;
        }

        http_response_code(200);
        echo "Webhook processed successfully for event: $eventType";

    } catch (Exception $e) {
        error_log('Webhook database error: ' . $e->getMessage());
        http_response_code(500);
        exit('Database error');
    }
} else {
    http_response_code(400);
    error_log('Invalid event structure in Coinbase webhook.');
    exit('Invalid event structure');
}
?>