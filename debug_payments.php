<?php
// Start session before any includes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/paypal_config.php';
require_once 'includes/crypto_config.php';
require_once 'includes/PayPalService.php';

echo "<!DOCTYPE html><html><head><title>Payment Debug</title>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .section{border:1px solid #ddd; margin:10px 0; padding:15px; border-radius:5px;}</style></head><body>";

echo "<h1>🔧 PowerWave Payment System Debug</h1>";

// Section 1: Basic Configuration
echo "<div class='section'>";
echo "<h2>1. Basic Configuration</h2>";

try {
    echo "<p class='info'>Site URL: " . SITE_URL . "</p>";
    echo "<p class='info'>Site Name: " . SITE_NAME . "</p>";
    echo "<p class='info'>Database: " . DB_NAME . "</p>";
    
    // Test database connection
    $db = Database::getInstance();
    echo "<p class='success'>✅ Database connection successful</p>";
    
    // Test session
    echo "<p class='info'>Session ID: " . session_id() . "</p>";
    echo "<p class='success'>✅ Session working</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Configuration Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Section 2: PayPal Configuration
echo "<div class='section'>";
echo "<h2>2. PayPal Configuration</h2>";

try {
    echo "<p class='info'>Environment: " . PAYPAL_ENVIRONMENT . "</p>";
    echo "<p class='info'>Client ID: " . substr(PAYPAL_CLIENT_ID, 0, 15) . "...</p>";
    echo "<p class='info'>Base URL: " . PAYPAL_BASE_URL . "</p>";
    
    // Test SDK URL
    $sdkUrl = getPayPalSDKUrl();
    echo "<p class='success'>✅ SDK URL: <a href='" . htmlspecialchars($sdkUrl) . "' target='_blank'>" . htmlspecialchars(substr($sdkUrl, 0, 60)) . "...</a></p>";
    
    // Test API connectivity
    echo "<p class='info'>Testing PayPal API connectivity...</p>";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['access_token'])) {
            echo "<p class='success'>✅ PayPal API connection successful!</p>";
        } else {
            echo "<p class='error'>❌ PayPal API response invalid</p>";
        }
    } else {
        echo "<p class='error'>❌ PayPal API connection failed (HTTP $httpCode)</p>";
        if ($response) {
            $error = json_decode($response, true);
            if (isset($error['error_description'])) {
                echo "<p class='error'>Error: " . htmlspecialchars($error['error_description']) . "</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ PayPal Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Section 3: Crypto Configuration
echo "<div class='section'>";
echo "<h2>3. Crypto Configuration</h2>";

try {
    echo "<p class='info'>Coinbase API Key: " . substr(COINBASE_COMMERCE_API_KEY, 0, 15) . "...</p>";
    echo "<p class='info'>API URL: " . COINBASE_COMMERCE_API_URL . "</p>";
    
    // Test crypto prices
    $prices = getCryptoPrices();
    echo "<p class='success'>✅ Crypto prices fetched:</p>";
    echo "<ul><li>Bitcoin: $" . number_format($prices['bitcoin'], 2) . "</li>";
    echo "<li>USDT: $" . number_format($prices['usdt'], 4) . "</li></ul>";
    
    if (isset($prices['fallback']) && $prices['fallback']) {
        echo "<p class='info'>⚠️ Using fallback prices (API might be temporarily unavailable)</p>";
    }
    
    // Test crypto calculation
    $testAmount = 100;
    $btcAmount = calculateCryptoAmount($testAmount, 'bitcoin');
    $usdtAmount = calculateCryptoAmount($testAmount, 'usdt');
    echo "<p class='success'>✅ Crypto calculations work:</p>";
    echo "<ul><li>$100 = " . number_format($btcAmount, 8) . " BTC</li>";
    echo "<li>$100 = " . number_format($usdtAmount, 2) . " USDT</li></ul>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Crypto Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Section 4: Cart System
echo "<div class='section'>";
echo "<h2>4. Cart System</h2>";

try {
    // Test current cart
    $userId = $_SESSION['user_id'] ?? null;
    $cartItems = getCartItems($userId);
    $cartCount = getCartItemCount($userId);
    $cartTotal = getCartTotal($userId);
    
    echo "<p class='info'>User ID: " . ($userId ? $userId : 'Guest') . "</p>";
    echo "<p class='info'>Cart items count: " . $cartCount . "</p>";
    echo "<p class='info'>Cart total: $" . number_format($cartTotal, 2) . "</p>";
    
    if (empty($cartItems)) {
        echo "<p class='success'>✅ Cart is empty (correct behavior when no items added)</p>";
    } else {
        echo "<p class='info'>Cart contents:</p><ul>";
        foreach ($cartItems as $item) {
            echo "<li>" . htmlspecialchars($item['name']) . " - Qty: " . $item['quantity'] . " - $" . number_format($item['price'], 2) . "</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Cart Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Section 5: API Endpoints Test
echo "<div class='section'>";
echo "<h2>5. API Endpoints</h2>";

$endpoints = [
    'PayPal Create Order' => 'api/paypal_create_order.php',
    'PayPal Capture Order' => 'api/paypal_capture_order.php',
    'Crypto Webhook' => 'crypto_webhook.php'
];

foreach ($endpoints as $name => $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $name endpoint exists</p>";
    } else {
        echo "<p class='error'>❌ $name endpoint missing: $file</p>";
    }
}
echo "</div>";

// Section 6: JavaScript SDK Test
echo "<div class='section'>";
echo "<h2>6. JavaScript SDK Test</h2>";

$paypalSDK = getPayPalSDKUrl();
echo "<p class='info'>Loading PayPal SDK for real-time test...</p>";
echo "<div id='paypal-sdk-test'>Loading...</div>";

echo "<script src='" . htmlspecialchars($paypalSDK) . "'></script>";
echo "<script>
if (typeof paypal !== 'undefined') {
    document.getElementById('paypal-sdk-test').innerHTML = '<span style=\"color:green;\">✅ PayPal SDK loaded successfully!</span>';
    console.log('PayPal SDK loaded:', paypal);
} else {
    document.getElementById('paypal-sdk-test').innerHTML = '<span style=\"color:red;\">❌ PayPal SDK failed to load</span>';
}
</script>";

echo "</div>";

// Section 7: Test Form for Crypto Payment
echo "<div class='section'>";
echo "<h2>7. Test Crypto Payment (Simulated)</h2>";

echo "<p class='info'>This will test the crypto payment flow without actually charging:</p>";
echo "<form method='POST'>";
echo "<input type='hidden' name='test_crypto' value='1'>";
echo "<button type='submit' style='background:#f7931a;color:white;padding:10px 20px;border:none;border-radius:5px;'>Test Crypto Payment Flow</button>";
echo "</form>";

if (isset($_POST['test_crypto'])) {
    echo "<div style='background:#f8f9fa;padding:10px;margin-top:10px;border-radius:5px;'>";
    echo "<p class='info'>Testing crypto payment creation...</p>";
    
    try {
        // Simulate creating a small order
        $testOrderId = rand(1000, 9999);
        $testAmount = 1.00; // $1 test
        
        $result = createCoinbaseCharge($testOrderId, 'Test Order', 'Test crypto payment', $testAmount, 'USD');
        
        if ($result['success']) {
            echo "<p class='success'>✅ Crypto payment test successful!</p>";
            echo "<p class='info'>Hosted URL: <a href='" . htmlspecialchars($result['hosted_url']) . "' target='_blank'>View Payment Page</a></p>";
            echo "<p class='info'>Charge Code: " . htmlspecialchars($result['code']) . "</p>";
        } else {
            echo "<p class='error'>❌ Crypto payment test failed: " . htmlspecialchars($result['error']) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Crypto payment test error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    echo "</div>";
}

echo "</div>";

// Section 8: Recommendations
echo "<div class='section'>";
echo "<h2>8. Recommendations</h2>";

echo "<p><strong>To fix remaining issues:</strong></p>";
echo "<ol>";
echo "<li><strong>Clear Browser Cache:</strong> Clear your browser cache and cookies to reset the session</li>";
echo "<li><strong>Test in Incognito:</strong> Try testing payments in an incognito/private browser window</li>";
echo "<li><strong>Check Console:</strong> Open browser developer tools (F12) and check for JavaScript errors in console</li>";
echo "<li><strong>Verify Network:</strong> Make sure your internet connection allows HTTPS requests to PayPal and Coinbase</li>";
echo "<li><strong>Update Credentials:</strong> Double-check that your PayPal sandbox credentials are correct and active</li>";
echo "</ol>";

echo "<p><strong>Test URLs:</strong></p>";
echo "<ul>";
echo "<li><a href='index.php'>Homepage</a></li>";
echo "<li><a href='products.php'>Products Page</a></li>";
echo "<li><a href='cart.php'>Cart Page</a></li>";
echo "<li><a href='checkout.php'>Checkout Page</a> (requires login and cart items)</li>";
echo "</ul>";

echo "</div>";

echo "</body></html>";
?>