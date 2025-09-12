<?php
// Start session before any includes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/crypto_config.php';

echo "<!DOCTYPE html><html><head><title>Crypto Payment Debug</title>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .section{border:1px solid #ddd; margin:10px 0; padding:15px; border-radius:5px;} .code{background:#f5f5f5;padding:10px;border-radius:3px;font-family:monospace;}</style></head><body>";

echo "<h1>🔧 Crypto Payment Debug Tool</h1>";

// Test 1: Basic Configuration
echo "<div class='section'>";
echo "<h2>1. Basic Configuration</h2>";

try {
    echo "<p class='info'>Coinbase API Key: " . substr(COINBASE_COMMERCE_API_KEY, 0, 20) . "...</p>";
    echo "<p class='info'>Webhook Secret: " . (defined('COINBASE_COMMERCE_WEBHOOK_SECRET') ? 'Defined' : 'Not defined') . "</p>";
    echo "<p class='success'>✅ Configuration loaded</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Configuration Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 2: Database Connection
echo "<div class='section'>";
echo "<h2>2. Database Connection</h2>";

try {
    $db = Database::getInstance();
    echo "<p class='success'>✅ Database connection successful</p>";
    
    // Check coinbase_charges table
    $tables = $db->fetchAll("SHOW TABLES LIKE 'coinbase_charges'");
    if (count($tables) > 0) {
        echo "<p class='success'>✅ coinbase_charges table exists</p>";
        
        // Show table structure
        $columns = $db->fetchAll("DESCRIBE coinbase_charges");
        echo "<p class='info'>Table structure:</p>";
        echo "<div class='code'>";
        foreach ($columns as $col) {
            echo $col['Field'] . " (" . $col['Type'] . ")<br>";
        }
        echo "</div>";
    } else {
        echo "<p class='error'>❌ coinbase_charges table missing</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 3: Simulate Order Creation
echo "<div class='section'>";
echo "<h2>3. Order Creation Test</h2>";

// Set up a mock user session for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Mock user ID
    echo "<p class='info'>Using mock user ID: 1</p>";
}

try {
    $db = Database::getInstance();
    $orderNumber = generateOrderNumber();
    $testAmount = 1.00;
    
    // Test order creation
    $orderData = [
        'user_id' => $_SESSION['user_id'],
        'order_number' => $orderNumber,
        'total_amount' => $testAmount,
        'shipping_cost' => 0.00,
        'tax_amount' => 0.00,
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => 'crypto',
        'shipping_address' => json_encode(['test' => 'address']),
        'billing_address' => json_encode(['test' => 'billing'])
    ];
    
    $orderId = $db->insert('orders', $orderData);
    echo "<p class='success'>✅ Test order created: ID $orderId, Number: $orderNumber</p>";
    
    // Clean up test order
    $db->delete('orders', 'id = ?', [$orderId]);
    echo "<p class='info'>Test order cleaned up</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Order Creation Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Try to get more specific error information
    if (strpos($e->getMessage(), 'Unknown column') !== false) {
        echo "<p class='info'>Checking orders table structure...</p>";
        try {
            $columns = $db->fetchAll("DESCRIBE orders");
            echo "<div class='code'>";
            foreach ($columns as $col) {
                echo $col['Field'] . " (" . $col['Type'] . ")<br>";
            }
            echo "</div>";
        } catch (Exception $e2) {
            echo "<p class='error'>Cannot describe orders table: " . htmlspecialchars($e2->getMessage()) . "</p>";
        }
    }
}
echo "</div>";

// Test 4: Coinbase Charge Creation
echo "<div class='section'>";
echo "<h2>4. Coinbase Charge Creation Test</h2>";

echo "<form method='POST'>";
echo "<input type='hidden' name='test_coinbase' value='1'>";
echo "<button type='submit' style='background:#f7931a;color:white;padding:10px 20px;border:none;border-radius:5px;'>Test Coinbase Charge Creation</button>";
echo "</form>";

if (isset($_POST['test_coinbase'])) {
    echo "<div style='background:#f8f9fa;padding:10px;margin-top:10px;border-radius:5px;'>";
    echo "<p class='info'>Testing Coinbase Commerce API...</p>";
    
    try {
        $testOrderId = rand(1000, 9999);
        $testAmount = 1.00;
        
        // Check if function exists
        if (!function_exists('createCoinbaseCharge')) {
            echo "<p class='error'>❌ createCoinbaseCharge function not found</p>";
        } else {
            echo "<p class='success'>✅ createCoinbaseCharge function exists</p>";
            
            $result = createCoinbaseCharge($testOrderId, 'Test Order', 'Test crypto payment', $testAmount, 'USD');
            
            echo "<p class='info'>API Response:</p>";
            echo "<div class='code'>" . json_encode($result, JSON_PRETTY_PRINT) . "</div>";
            
            if ($result['success']) {
                echo "<p class='success'>✅ Coinbase charge created successfully!</p>";
                echo "<p class='info'>Hosted URL: <a href='" . htmlspecialchars($result['hosted_url']) . "' target='_blank'>View Payment Page</a></p>";
            } else {
                echo "<p class='error'>❌ Coinbase charge failed: " . htmlspecialchars($result['error']) . "</p>";
                
                // Provide specific help for common errors
                if (strpos($result['error'], 'settlement') !== false) {
                    echo "<div style='background:#fff3cd;padding:10px;border-radius:5px;margin-top:10px;'>";
                    echo "<p><strong>⚠️ Coinbase Commerce Setup Required:</strong></p>";
                    echo "<ol>";
                    echo "<li>Log into your Coinbase Commerce account at <a href='https://commerce.coinbase.com' target='_blank'>commerce.coinbase.com</a></li>";
                    echo "<li>Go to Settings → Business Information</li>";
                    echo "<li>Add your business address and settlement information</li>";
                    echo "<li>Complete the merchant verification process</li>";
                    echo "</ol>";
                    echo "</div>";
                } elseif (strpos($result['error'], 'API key') !== false) {
                    echo "<p class='info'>Check your Coinbase Commerce API key in includes/crypto_config.php</p>";
                }
            }
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Exception during test: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p class='info'>Stack trace: " . htmlspecialchars($e->getTraceAsString()) . "</p>";
    }
    echo "</div>";
}

echo "</div>";

// Test 5: Full Crypto Payment Simulation
echo "<div class='section'>";
echo "<h2>5. Full Crypto Payment Flow Simulation</h2>";

echo "<form method='POST'>";
echo "<input type='hidden' name='test_full_flow' value='1'>";
echo "<button type='submit' style='background:#28a745;color:white;padding:10px 20px;border:none;border-radius:5px;'>Simulate Full Crypto Payment Flow</button>";
echo "</form>";

if (isset($_POST['test_full_flow'])) {
    echo "<div style='background:#f8f9fa;padding:10px;margin-top:10px;border-radius:5px;'>";
    echo "<p class='info'>Simulating full crypto payment flow...</p>";
    
    try {
        // Step 1: Create order
        $db = Database::getInstance();
        $orderNumber = generateOrderNumber();
        $testAmount = 5.00;
        
        echo "<p class='info'>Step 1: Creating order...</p>";
        $orderData = [
            'user_id' => $_SESSION['user_id'],
            'order_number' => $orderNumber,
            'total_amount' => $testAmount,
            'shipping_cost' => 0.00,
            'tax_amount' => 0.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'crypto',
            'shipping_address' => json_encode(['test' => 'address']),
            'billing_address' => json_encode(['test' => 'billing'])
        ];
        
        $orderId = $db->insert('orders', $orderData);
        echo "<p class='success'>✅ Order created: ID $orderId</p>";
        
        // Step 2: Create Coinbase charge
        echo "<p class='info'>Step 2: Creating Coinbase charge...</p>";
        $charge = createCoinbaseCharge($orderId, 'Order ' . $orderNumber, 'Payment for order ' . $orderNumber, $testAmount, 'USD');
        
        if ($charge['success']) {
            echo "<p class='success'>✅ Crypto payment flow successful!</p>";
            echo "<p class='info'>Hosted URL: <a href='" . htmlspecialchars($charge['hosted_url']) . "' target='_blank'>View Payment Page</a></p>";
            echo "<p class='info'>Charge Code: " . htmlspecialchars($charge['code']) . "</p>";
            
            // Return what would be sent to the frontend
            $response = ['success' => true, 'hosted_url' => $charge['hosted_url'], 'charge_code' => $charge['code']];
            echo "<p class='info'>Response to frontend:</p>";
            echo "<div class='code'>" . json_encode($response, JSON_PRETTY_PRINT) . "</div>";
            
        } else {
            echo "<p class='error'>❌ Crypto payment flow failed: " . htmlspecialchars($charge['error']) . "</p>";
            
            // Return error response
            $response = ['success' => false, 'error' => $charge['error']];
            echo "<p class='info'>Error response to frontend:</p>";
            echo "<div class='code'>" . json_encode($response, JSON_PRETTY_PRINT) . "</div>";
        }
        
        // Clean up
        echo "<p class='info'>Cleaning up test data...</p>";
        $db->delete('orders', 'id = ?', [$orderId]);
        $db->delete('coinbase_charges', 'order_id = ?', [$orderId]);
        echo "<p class='success'>✅ Cleanup completed</p>";
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Full flow error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<div class='code'>" . htmlspecialchars($e->getTraceAsString()) . "</div>";
    }
    
    echo "</div>";
}

echo "</div>";

// Test 6: Manual Error Reproduction
echo "<div class='section'>";
echo "<h2>6. Manual Error Reproduction</h2>";
echo "<p>To test the exact error from your browser:</p>";
echo "<ol>";
echo "<li>Open your website in a browser: <a href='http://localhost:8000' target='_blank'>http://localhost:8000</a></li>";
echo "<li>Add items to cart and go to checkout</li>";
echo "<li>Select crypto payment and click submit</li>";
echo "<li>Open browser developer tools (F12) and check the Network tab for the AJAX request</li>";
echo "<li>Look for the response from the crypto payment AJAX call</li>";
echo "</ol>";
echo "<p><strong>Common debugging steps:</strong></p>";
echo "<ul>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "<li>Verify CSRF token is being sent correctly</li>";
echo "<li>Check network tab for the actual server response</li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>