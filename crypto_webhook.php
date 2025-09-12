<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/crypto_config.php';

// Get the request body
$payload = file_get_contents('php://input');

// Robust header retrieval for various SAPIs
if (function_exists('getallheaders')) {
    $rawHeaders = getallheaders();
} else {
    // Fallback: collect HTTP_ headers from $_SERVER
    $rawHeaders = [];
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0) {
            $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
            $rawHeaders[$name] = $value;
        }
    }
}

// Case-insensitive lookup for the Coinbase signature header
$signatureHeader = '';
foreach ($rawHeaders as $hName => $hValue) {
    if (strcasecmp($hName, 'X-Cc-Webhook-Signature') === 0 || strcasecmp($hName, 'X-Cc-Webhook-Signature-SHA256') === 0) {
        $signatureHeader = $hValue;
        break;
    }
}

// Ensure webhook secret is configured
if (!defined('COINBASE_COMMERCE_WEBHOOK_SECRET') || empty(COINBASE_COMMERCE_WEBHOOK_SECRET)) {
    http_response_code(500);
    error_log('Coinbase webhook secret not configured.');
    exit('Webhook configuration error');
}

// Compute signature and compare in timing-safe manner
$computedSignature = hash_hmac('sha256', $payload ?: '', COINBASE_COMMERCE_WEBHOOK_SECRET);

// If header missing or signatures mismatch, reject
if (empty($signatureHeader) || !hash_equals($computedSignature, (string)$signatureHeader)) {
    http_response_code(400);
    error_log('Invalid Coinbase webhook signature or missing header.');
    exit('Invalid signature');
}

// Decode the event
$event = json_decode($payload, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    error_log('Invalid JSON in Coinbase webhook: ' . json_last_error_msg());
    exit('Invalid JSON');
}

// Handle the event
if (isset($event['event']['type']) && isset($event['event']['data'])) {
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
                if ($chargeCode) {
                    $db->update('coinbase_charges', ['status' => 'CONFIRMED'], 'charge_code = ?', [$chargeCode]);
                }
                error_log("Order $orderId successfully updated to PAID/PROCESSING via Coinbase webhook.");
                break;

            case 'charge:failed':
                // Payment failed
                $db->update('orders',
                    ['payment_status' => 'failed', 'status' => 'cancelled'],
                    'id = ?',
                    [$orderId]
                );
                if ($chargeCode) {
                    $db->update('coinbase_charges', ['status' => 'FAILED'], 'charge_code = ?', [$chargeCode]);
                }
                error_log("Order $orderId marked as FAILED/CANCELLED via Coinbase webhook.");
                break;

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