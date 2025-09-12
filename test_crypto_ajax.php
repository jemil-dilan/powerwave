<?php
// Start session before any includes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/crypto_config.php';

// Set up test user if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

$userId = $_SESSION['user_id'];

// Handle the AJAX crypto payment test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'test_crypto_payment') {
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        $db = Database::getInstance();
        
        // Create a test order
        $orderNumber = generateOrderNumber();
        $testAmount = 10.00; // $10 test
        $shipping = 0.00;
        $tax = 0.00;
        $grandTotal = $testAmount + $shipping + $tax;
        
        $orderData = [
            'user_id' => $userId,
            'order_number' => $orderNumber,
            'total_amount' => $grandTotal,
            'shipping_cost' => $shipping,
            'tax_amount' => $tax,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'crypto',
            'shipping_address' => json_encode([
                'first_name' => 'Test',
                'last_name' => 'User',
                'address' => '123 Test St',
                'city' => 'Test City',
                'state' => 'TC',
                'zip' => '12345',
                'country' => 'United States'
            ]),
            'billing_address' => json_encode([
                'first_name' => 'Test',
                'last_name' => 'User',
                'address' => '123 Test St',
                'city' => 'Test City',
                'state' => 'TC',
                'zip' => '12345',
                'country' => 'United States'
            ])
        ];
        
        $orderId = $db->insert('orders', $orderData);
        
        if (!$orderId) {
            throw new Exception('Failed to create order');
        }
        
        // Create Coinbase charge
        $charge = createCoinbaseCharge($orderId, 'Test Order ' . $orderNumber, 'Test crypto payment for order ' . $orderNumber, $grandTotal, 'USD');
        
        if ($charge['success']) {
            // Clean up test order after successful test
            $db->delete('orders', 'id = ?', [$orderId]);
            $db->delete('coinbase_charges', 'order_id = ?', [$orderId]);
            
            echo json_encode([
                'success' => true, 
                'hosted_url' => $charge['hosted_url'], 
                'charge_code' => $charge['code'],
                'message' => 'Test crypto payment created successfully! (Test data cleaned up)'
            ]);
        } else {
            // Clean up test order on failure
            $db->delete('orders', 'id = ?', [$orderId]);
            
            echo json_encode([
                'success' => false, 
                'error' => $charge['error'] ?? 'Failed to create charge',
                'details' => 'Order creation succeeded but Coinbase charge failed'
            ]);
        }
        
    } catch (Exception $e) {
        error_log('Test crypto payment error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        
        echo json_encode([
            'success' => false, 
            'error' => 'Server error: ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
    
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Crypto Payment AJAX Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4fdd4; border: 1px solid #4CAF50; }
        .error { background: #ffebee; border: 1px solid #f44336; }
        .info { background: #e3f2fd; border: 1px solid #2196F3; }
        button { background: #f7931a; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #e8851e; }
        button:disabled { background: #ccc; cursor: not-allowed; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 3px; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>🔧 Crypto Payment AJAX Test</h1>
    
    <div class="info result">
        <p><strong>This test simulates the exact AJAX request that happens when you click the crypto payment button in checkout.</strong></p>
        <p>It will create a test order, attempt to create a Coinbase charge, and then clean up the test data.</p>
    </div>
    
    <button onclick="testCryptoPayment()" id="testBtn">🚀 Test Crypto Payment AJAX</button>
    
    <div id="result" style="display: none;"></div>
    
    <script>
        function testCryptoPayment() {
            const btn = document.getElementById('testBtn');
            const result = document.getElementById('result');
            
            btn.disabled = true;
            btn.innerHTML = '⏳ Testing...';
            result.style.display = 'none';
            
            const formData = new FormData();
            formData.append('action', 'test_crypto_payment');
            
            fetch(window.location.pathname, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server response:', data);
                
                result.style.display = 'block';
                
                if (data.success) {
                    result.className = 'result success';
                    result.innerHTML = `
                        <h3>✅ Success!</h3>
                        <p>${data.message}</p>
                        <p><strong>Charge Code:</strong> ${data.charge_code}</p>
                        <p><strong>Hosted URL:</strong> <a href="${data.hosted_url}" target="_blank">View Payment Page</a></p>
                    `;
                } else {
                    result.className = 'result error';
                    result.innerHTML = `
                        <h3>❌ Failed</h3>
                        <p><strong>Error:</strong> ${data.error}</p>
                        ${data.details ? '<p><strong>Details:</strong> ' + data.details + '</p>' : ''}
                        ${data.file ? '<p><strong>File:</strong> ' + data.file + ':' + data.line + '</p>' : ''}
                    `;
                }
                
                // Show raw response for debugging
                const rawResponse = document.createElement('div');
                rawResponse.innerHTML = '<h4>Raw Response:</h4><div class="code">' + JSON.stringify(data, null, 2) + '</div>';
                result.appendChild(rawResponse);
            })
            .catch(error => {
                console.error('Request failed:', error);
                
                result.style.display = 'block';
                result.className = 'result error';
                result.innerHTML = `
                    <h3>❌ Request Failed</h3>
                    <p><strong>Error:</strong> ${error.message}</p>
                    <p>Check the browser console for more details.</p>
                `;
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '🚀 Test Crypto Payment AJAX';
            });
        }
    </script>
    
    <div class="info result" style="margin-top: 30px;">
        <h3>🔍 How to debug the real checkout:</h3>
        <ol>
            <li>Start the development server: <code>./start_server.sh</code></li>
            <li>Open <a href="http://localhost:8000" target="_blank">http://localhost:8000</a> in your browser</li>
            <li>Add some products to cart</li>
            <li>Go to checkout and try crypto payment</li>
            <li>Open developer tools (F12) and check the Network tab for AJAX requests</li>
            <li>Look for the POST request to <code>checkout.php</code> with <code>action=create_crypto_charge</code></li>
            <li>Check the response to see the exact error</li>
        </ol>
    </div>
</body>
</html>