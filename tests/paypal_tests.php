<?php
/**
 * PayPal Integration Test Suite
 * Run this file to test PayPal functionality
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/paypal_config.php';
require_once '../includes/PayPalService.php';

// Prevent session headers issue
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Test results array
$testResults = [];

echo "<h1>🧪 PayPal Integration Test Suite</h1>";
echo "<p>Testing PayPal integration components...</p>";

// Test 1: PayPal Configuration
echo "<h2>1. PayPal Configuration Test</h2>";
$configTest = testPayPalConfiguration();
$testResults['config'] = $configTest;
displayTestResult($configTest, 'PayPal Configuration');

// Test 2: PayPal Service Class
echo "<h2>2. PayPal Service Class Test</h2>";
$serviceTest = testPayPalService();
$testResults['service'] = $serviceTest;
displayTestResult($serviceTest, 'PayPal Service Class');

// Test 3: Database Schema
echo "<h2>3. Database Schema Test</h2>";
$databaseTest = testDatabaseSchema();
$testResults['database'] = $databaseTest;
displayTestResult($databaseTest, 'Database Schema');

// Test 4: API Endpoints (simulated)
echo "<h2>4. API Endpoints Test</h2>";
$apiTest = testAPIEndpoints();
$testResults['api'] = $apiTest;
displayTestResult($apiTest, 'API Endpoints');

// Test 5: Helper Functions
echo "<h2>5. Helper Functions Test</h2>";
$helpersTest = testHelperFunctions();
$testResults['helpers'] = $helpersTest;
displayTestResult($helpersTest, 'Helper Functions');

// Summary
echo "<h2>📊 Test Summary</h2>";
$totalTests = count($testResults);
$passedTests = count(array_filter($testResults, function($result) { return $result['success']; }));

echo "<div style='background: " . ($passedTests === $totalTests ? '#d1fae5' : '#fee2e2') . "; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>Results: {$passedTests}/{$totalTests} tests passed</h3>";

foreach ($testResults as $testName => $result) {
    $icon = $result['success'] ? '✅' : '❌';
    echo "<p>{$icon} " . ucfirst($testName) . ": " . $result['message'] . "</p>";
}

if ($passedTests === $totalTests) {
    echo "<p><strong>🎉 All tests passed! PayPal integration is ready.</strong></p>";
} else {
    echo "<p><strong>⚠️ Some tests failed. Please check the configuration and setup.</strong></p>";
}
echo "</div>";

/**
 * Test PayPal Configuration
 */
function testPayPalConfiguration() {
    try {
        // Check if constants are defined
        $required = ['PAYPAL_CLIENT_ID', 'PAYPAL_CLIENT_SECRET', 'PAYPAL_BASE_URL', 'PAYPAL_CURRENCY'];
        
        foreach ($required as $constant) {
            if (!defined($constant)) {
                throw new Exception("Missing constant: {$constant}");
            }
        }
        
        // Check if credentials are not placeholders
        if (strpos(PAYPAL_CLIENT_ID, 'YOUR_') === 0 || strpos(PAYPAL_CLIENT_ID, '{{') === 0) {
            return ['success' => false, 'message' => 'PayPal Client ID is still a placeholder'];
        }
        
        // Check environment setting
        if (!in_array(PAYPAL_ENVIRONMENT, ['sandbox', 'production'])) {
            throw new Exception('Invalid PayPal environment');
        }
        
        return ['success' => true, 'message' => 'Configuration is valid (' . PAYPAL_ENVIRONMENT . ' mode)'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Test PayPal Service Class
 */
function testPayPalService() {
    try {
        // Test class instantiation
        $paypalService = new PayPalService();
        
        if (!$paypalService) {
            throw new Exception('Failed to create PayPal service instance');
        }
        
        // Test if methods exist
        $requiredMethods = ['createOrder', 'captureOrder', 'getOrderDetails'];
        
        foreach ($requiredMethods as $method) {
            if (!method_exists($paypalService, $method)) {
                throw new Exception("Missing method: {$method}");
            }
        }
        
        return ['success' => true, 'message' => 'PayPal service class is properly structured'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Test Database Schema
 */
function testDatabaseSchema() {
    try {
        $db = Database::getInstance();
        
        // Check if orders table has required PayPal fields
        $orderColumns = $db->fetchAll("DESCRIBE orders");
        $columnNames = array_column($orderColumns, 'Field');
        
        $requiredFields = ['payment_transaction_id', 'payment_details'];
        
        foreach ($requiredFields as $field) {
            if (!in_array($field, $columnNames)) {
                throw new Exception("Missing orders table field: {$field}");
            }
        }
        
        // Check if PayPal transactions table exists
        try {
            $paypalColumns = $db->fetchAll("DESCRIBE paypal_transactions");
            if (empty($paypalColumns)) {
                throw new Exception('PayPal transactions table is empty');
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'PayPal transactions table does not exist - run updated database.sql'];
        }
        
        return ['success' => true, 'message' => 'Database schema supports PayPal integration'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Test API Endpoints (simulated)
 */
function testAPIEndpoints() {
    try {
        // Check if API files exist
        $apiFiles = [
            '../api/paypal_create_order.php',
            '../api/paypal_capture_order.php'
        ];
        
        foreach ($apiFiles as $file) {
            if (!file_exists($file)) {
                throw new Exception("Missing API file: " . basename($file));
            }
        }
        
        // Check if files are readable
        foreach ($apiFiles as $file) {
            if (!is_readable($file)) {
                throw new Exception("API file not readable: " . basename($file));
            }
        }
        
        return ['success' => true, 'message' => 'API endpoints are properly configured'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Test Helper Functions
 */
function testHelperFunctions() {
    try {
        // Test getPayPalSDKUrl function
        if (!function_exists('getPayPalSDKUrl')) {
            throw new Exception('getPayPalSDKUrl function not found');
        }
        
        $sdkUrl = getPayPalSDKUrl();
        if (empty($sdkUrl) || !filter_var($sdkUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid PayPal SDK URL generated');
        }
        
        // Test if URL contains required parameters
        if (strpos($sdkUrl, 'client-id') === false) {
            throw new Exception('PayPal SDK URL missing client-id parameter');
        }
        
        return ['success' => true, 'message' => 'Helper functions are working correctly'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Display test result
 */
function displayTestResult($result, $testName) {
    $icon = $result['success'] ? '✅' : '❌';
    $color = $result['success'] ? '#10b981' : '#ef4444';
    
    echo "<div style='background: white; padding: 16px; border-radius: 8px; border-left: 4px solid {$color}; margin: 16px 0;'>";
    echo "<p><strong>{$icon} {$testName}:</strong> {$result['message']}</p>";
    echo "</div>";
}

// Add some CSS for better presentation
echo "<style>
body { 
    font-family: Arial, sans-serif; 
    margin: 20px; 
    background: #f8fafc; 
    line-height: 1.6;
}
h1, h2 { 
    color: #1f2937; 
}
h1 { 
    border-bottom: 3px solid #0ea5e9; 
    padding-bottom: 10px; 
}
h2 { 
    color: #0ea5e9; 
    margin-top: 30px;
}
</style>";
?>
