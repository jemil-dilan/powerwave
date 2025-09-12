<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/paypal_config.php';
require_once 'includes/crypto_config.php';
require_once 'includes/PayPalService.php';

echo "<h1>🧪 Payment System Test</h1>\n";
echo "<style>body{font-family:Arial;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>\n";

// Test 1: PayPal Configuration
echo "<h2>1. PayPal Configuration Test</h2>\n";

try {
    // Check if constants are defined
    if (!defined('PAYPAL_CLIENT_ID') || !defined('PAYPAL_CLIENT_SECRET')) {
        echo "<p class='error'>❌ PayPal constants not defined</p>\n";
    } else {
        echo "<p class='success'>✅ PayPal constants defined</p>\n";
        echo "<p class='info'>Environment: " . PAYPAL_ENVIRONMENT . "</p>\n";
        echo "<p class='info'>Client ID: " . substr(PAYPAL_CLIENT_ID, 0, 10) . "...</p>\n";
        
        // Test SDK URL generation
        $sdkUrl = getPayPalSDKUrl();
        if (!empty($sdkUrl) && filter_var($sdkUrl, FILTER_VALIDATE_URL)) {
            echo "<p class='success'>✅ PayPal SDK URL generated correctly</p>\n";
            echo "<p class='info'>SDK URL: <a href='" . htmlspecialchars($sdkUrl) . "' target='_blank'>" . htmlspecialchars(substr($sdkUrl, 0, 80)) . "...</a></p>\n";
        } else {
            echo "<p class='error'>❌ PayPal SDK URL generation failed</p>\n";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ PayPal test error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// Test 2: Crypto Configuration
echo "<h2>2. Crypto Configuration Test</h2>\n";

try {
    if (!defined('COINBASE_COMMERCE_API_KEY') || empty(COINBASE_COMMERCE_API_KEY)) {
        echo "<p class='error'>❌ Coinbase API key not defined</p>\n";
    } else {
        echo "<p class='success'>✅ Coinbase API key defined</p>\n";
        echo "<p class='info'>API Key: " . substr(COINBASE_COMMERCE_API_KEY, 0, 10) . "...</p>\n";
        
        // Test crypto price fetching
        $prices = getCryptoPrices();
        if ($prices) {
            echo "<p class='success'>✅ Crypto prices fetched successfully</p>\n";
            echo "<p class='info'>Bitcoin: $" . number_format($prices['bitcoin'], 2) . "</p>\n";
            echo "<p class='info'>USDT: $" . number_format($prices['usdt'], 4) . "</p>\n";
            if (isset($prices['fallback']) && $prices['fallback']) {
                echo "<p class='info'>⚠️ Using fallback prices (API unavailable)</p>\n";
            }
        } else {
            echo "<p class='error'>❌ Failed to get crypto prices</p>\n";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Crypto test error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// Test 3: Database Tables
echo "<h2>3. Database Tables Test</h2>\n";

try {
    $db = Database::getInstance();
    
    // Check for PayPal tables
    $tables = $db->fetchAll("SHOW TABLES");
    $tableNames = [];
    foreach ($tables as $table) {
        $tableNames[] = array_values($table)[0];
    }
    
    $requiredTables = ['orders', 'paypal_transactions', 'coinbase_charges'];
    
    foreach ($requiredTables as $table) {
        if (in_array($table, $tableNames)) {
            echo "<p class='success'>✅ Table '$table' exists</p>\n";
        } else {
            echo "<p class='error'>❌ Table '$table' missing</p>\n";
            if ($table === 'coinbase_charges') {
                echo "<p class='info'>Run fix_crypto_table.php to create this table</p>\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Database test error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// Test 4: API Endpoints
echo "<h2>4. API Endpoints Test</h2>\n";

$apiFiles = [
    'api/paypal_create_order.php' => 'PayPal Create Order',
    'api/paypal_capture_order.php' => 'PayPal Capture Order',
    'crypto_webhook.php' => 'Crypto Webhook Handler'
];

foreach ($apiFiles as $file => $name) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $name endpoint exists</p>\n";
    } else {
        echo "<p class='error'>❌ $name endpoint missing</p>\n";
    }
}

echo "<h2>Summary</h2>\n";
echo "<p>Run this test after making configuration changes to verify everything is working correctly.</p>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ul>\n";
echo "<li>If crypto table is missing, run: <code>php fix_crypto_table.php</code></li>\n";
echo "<li>Test PayPal payments on checkout page</li>\n";
echo "<li>Test crypto payments on checkout page</li>\n";
echo "<li>Monitor error logs for any issues</li>\n";
echo "</ul>\n";

?>