<?php
/**
 * Complete Functionality Test Suite
 * Tests all aspects of the outboard motors website
 */

// Prevent session issues
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$testResults = [];
$overallStatus = true;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Functionality Tests</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8fafc; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 32px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #0ea5e9; border-bottom: 3px solid #0ea5e9; padding-bottom: 10px; }
        h2 { color: #1f2937; margin-top: 30px; }
        .test-result { padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid; }
        .test-pass { background: #d1fae5; border-left-color: #10b981; }
        .test-fail { background: #fee2e2; border-left-color: #ef4444; }
        .test-warning { background: #fef3c7; border-left-color: #f59e0b; }
        .test-info { background: #e0f2fe; border-left-color: #0ea5e9; }
        .code { background: #1f2937; color: #e5e7eb; padding: 10px; border-radius: 4px; font-family: monospace; margin: 5px 0; }
        .summary { background: #f1f5f9; padding: 20px; border-radius: 8px; margin: 20px 0; }
        pre { background: #f8fafc; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
        .test-section { margin: 30px 0; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Complete Project Functionality Test</h1>
        <p>Comprehensive testing of all website functionality and bug detection.</p>
        
        <?php
        // Test 1: Core Configuration
        testCoreConfiguration();
        
        // Test 2: Database Connectivity and Schema
        testDatabaseSchema();
        
        // Test 3: User Authentication System
        testAuthenticationSystem();
        
        // Test 4: Product Management
        testProductManagement();
        
        // Test 5: Shopping Cart Functionality
        testShoppingCart();
        
        // Test 6: Order Processing
        testOrderProcessing();
        
        // Test 7: PayPal Integration
        testPayPalIntegration();
        
        // Test 8: Admin Panel
        testAdminPanel();
        
        // Test 9: File Upload System
        testFileUploadSystem();
        
        // Test 10: Security Features
        testSecurityFeatures();
        
        // Test 11: Frontend Functionality
        testFrontendFunctionality();
        
        // Display summary
        displayTestSummary();
        ?>
    </div>
</body>
</html>

<?php

function testCoreConfiguration() {
    global $testResults, $overallStatus;
    
    echo "<div class='test-section'>";
    echo "<h2>🔧 Core Configuration Tests</h2>";
    
    try {
        require_once 'includes/config.php';
        addTestResult('✅ Config file loaded successfully', 'pass');
        
        // Check all required constants
        $requiredConstants = [
            'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS',
            'SITE_URL', 'SITE_NAME', 'SITE_EMAIL',
            'UPLOAD_PATH', 'MAX_FILE_SIZE', 'PRODUCTS_PER_PAGE'
        ];
        
        foreach ($requiredConstants as $constant) {
            if (defined($constant)) {
                addTestResult("✅ Constant '$constant' defined", 'pass');
            } else {
                addTestResult("❌ Missing constant '$constant'", 'fail');
            }
        }
        
        // Check upload directory
        if (is_dir(UPLOAD_PATH)) {
            addTestResult('✅ Upload directory exists', 'pass');
            if (is_writable(UPLOAD_PATH)) {
                addTestResult('✅ Upload directory is writable', 'pass');
            } else {
                addTestResult('❌ Upload directory not writable', 'fail');
            }
        } else {
            addTestResult('❌ Upload directory missing', 'fail');
        }
        
    } catch (Exception $e) {
        addTestResult('❌ Config error: ' . $e->getMessage(), 'fail');
    }
    echo "</div>";
}

function testDatabaseSchema() {
    global $testResults, $overallStatus;
    
    echo "<div class='test-section'>";
    echo "<h2>🗄️ Database Schema Tests</h2>";
    
    try {
        require_once 'includes/database.php';
        $db = Database::getInstance();
        addTestResult('✅ Database connection successful', 'pass');
        
        // Test all required tables
        $requiredTables = [
            'users', 'products', 'categories', 'brands', 'orders', 
            'order_items', 'cart', 'paypal_transactions', 'accessories',
            'contact_messages', 'newsletter_subscriptions', 'product_images',
            'wishlist', 'reviews'
        ];
        
        foreach ($requiredTables as $table) {
            try {
                $result = $db->fetchOne("SHOW TABLES LIKE '$table'");
                if ($result) {
                    addTestResult("✅ Table '$table' exists", 'pass');
                    
                    // Test table structure for critical tables
                    if ($table === 'orders') {
                        $columns = $db->fetchAll("SHOW COLUMNS FROM orders");
                        $columnNames = array_column($columns, 'Field');
                        if (in_array('payment_transaction_id', $columnNames)) {
                            addTestResult('✅ Orders table has PayPal fields', 'pass');
                        } else {
                            addTestResult('⚠️ Orders table missing PayPal fields', 'warning');
                        }
                    }
                } else {
                    addTestResult("❌ Table '$table' missing", 'fail');
                }
            } catch (Exception $e) {
                addTestResult("❌ Error checking table '$table': " . $e->getMessage(), 'fail');
            }
        }
        
        // Test sample data
        $userCount = $db->fetchColumn("SELECT COUNT(*) FROM users");
        $productCount = $db->fetchColumn("SELECT COUNT(*) FROM products");
        $categoryCount = $db->fetchColumn("SELECT COUNT(*) FROM categories");
        
        addTestResult("ℹ️ Database contains: $userCount users, $productCount products, $categoryCount categories", 'info');
        
    } catch (Exception $e) {
        addTestResult('❌ Database connection failed: ' . $e->getMessage(), 'fail');
    }
    echo "</div>";
}

function testAuthenticationSystem() {
    global $testResults, $overallStatus;
    
    echo "<div class='test-section'>";
    echo "<h2>🔐 Authentication System Tests</h2>";
    
    try {
        require_once 'includes/functions.php';
        
        // Test password hashing
        $testPassword = 'testpassword123';
        $hash = hashPassword($testPassword);
        if (password_verify($testPassword, $hash)) {
            addTestResult('✅ Password hashing works correctly', 'pass');
        } else {
            addTestResult('❌ Password hashing failed', 'fail');
        }
        
        // Test admin account
        $db = Database::getInstance();
        $admin = $db->fetchOne("SELECT * FROM users WHERE email = 'gonzila@gmail.com'");
        if ($admin) {
            addTestResult('✅ Admin account exists', 'pass');
            if (password_verify('gonzilaib', $admin['password'])) {
                addTestResult('✅ Admin password verification works', 'pass');
            } else {
                addTestResult('❌ Admin password verification failed', 'fail');
            }
        } else {
            addTestResult('❌ Admin account not found', 'fail');
        }
        
        // Test CSRF token generation
        $token = generateCSRFToken();
        if (!empty($token) && strlen($token) === 64) {
            addTestResult('✅ CSRF token generation works', 'pass');
        } else {
            addTestResult('❌ CSRF token generation failed', 'fail');
        }
        
    } catch (Exception $e) {
        addTestResult('❌ Authentication test error: ' . $e->getMessage(), 'fail');
    }
    echo "</div>";
}

function testProductManagement() {
    global $testResults, $overallStatus;
    
    echo "<div class='test-section'>";
    echo "<h2>📦 Product Management Tests</h2>";
    
    try {
        require_once 'includes/functions.php';
        $db = Database::getInstance();
        
        // Test product retrieval functions
        $featuredProducts = getFeaturedProducts(3);
        if (is_array($featuredProducts)) {
            addTestResult('✅ getFeaturedProducts() works', 'pass');
        } else {
            addTestResult('❌ getFeaturedProducts() failed', 'fail');
        }
        
        // Test product search
        $searchResults = searchProducts('yamaha', null, null, null, null, 5, 0);
        if (is_array($searchResults) && isset($searchResults['products'])) {
            addTestResult('✅ Product search function works', 'pass');
        } else {
            addTestResult('❌ Product search function failed', 'fail');
        }
        
        // Test image URL generation
        $imageUrl = getProductImageUrl('test-image.png');
        if (strpos($imageUrl, SITE_URL) !== false) {
            addTestResult('✅ Product image URL generation works', 'pass');
        } else {
            addTestResult('❌ Product image URL generation failed', 'fail');
        }
        
        // Test categories and brands
        $categories = getAllCategories();
        $brands = getAllBrands();
        
        if (!empty($categories)) {
            addTestResult('✅ Categories loaded successfully', 'pass');
        } else {
            addTestResult('⚠️ No categories found', 'warning');
        }
        
        if (!empty($brands)) {
            addTestResult('✅ Brands loaded successfully', 'pass');
        } else {
            addTestResult('⚠️ No brands found', 'warning');
        }
        
    } catch (Exception $e) {
        addTestResult('❌ Product management test error: ' . $e->getMessage(), 'fail');
    }
    echo "</div>";
}

function testShoppingCart() {
    global $testResults, $overallStatus;
    
    echo "<div class='test-section'>";
    echo "<h2>🛒 Shopping Cart Tests</h2>";
    
    try {
        require_once 'includes/functions.php';
        $db = Database::getInstance();
        
        // Test cart functions exist
        $cartFunctions = ['addToCart', 'getCartItems', 'getCartTotal', 'getCartItemCount'];
        foreach ($cartFunctions as $function) {
            if (function_exists($function)) {
                addTestResult("✅ Function '$function' exists", 'pass');
            } else {
                addTestResult("❌ Function '$function' missing", 'fail');
            }
        }
        
        // Test cart with session (guest user)
        $sessionId = session_id();
        if (!empty($sessionId)) {
            addTestResult('✅ Session ID available for guest cart', 'pass');
        } else {
            addTestResult('❌ No session ID for guest cart', 'fail');
        }
        
        // Test cart item count for guest
        $guestCartCount = getCartItemCount(null);
        if (is_numeric($guestCartCount)) {
            addTestResult('✅ Guest cart count function works', 'pass');
        } else {
            addTestResult('❌ Guest cart count function failed', 'fail');
        }
        
    } catch (Exception $e) {
        addTestResult('❌ Shopping cart test error: ' . $e->getMessage(), 'fail');
    }
    echo "</div>";
}

function testOrderProcessing() {
    global $testResults, $overallStatus;
    
    echo "<div class='test-section'>";
    echo "<h2>📋 Order Processing Tests</h2>";
    
    try {
        require_once 'includes/functions.php';
        
        // Test order number generation
        $orderNumber = generateOrderNumber();
        if (preg_match('/^ORD-\d{4}-[A-Z0-9]{8}$/', $orderNumber)) {
            addTestResult('✅ Order number generation works', 'pass');
        } else {
            addTestResult("❌ Invalid order number format: $orderNumber", 'fail');
        }
        
        // Test email function
        if (function_exists('sendEmail')) {
            addTestResult('✅ Email function exists', 'pass');
        } else {
            addTestResult('❌ Email function missing', 'fail');
        }
        
        // Test price formatting
        $formattedPrice = formatPrice(1234.56);
        if ($formattedPrice === '$1,234.56') {
            addTestResult('✅ Price formatting works correctly', 'pass');
        } else {
            addTestResult("❌ Price formatting incorrect: $formattedPrice", 'fail');
        }
        
    } catch (Exception $e) {
        addTestResult('❌ Order processing test error: ' . $e->getMessage(), 'fail');
    }
    echo "</div>";
}

function testPayPalIntegration() {
    global $testResults, $overallStatus;
    
    echo "<div class='test-section'>";
    echo "<h2>💳 PayPal Integration Tests</h2>";
    
    try {
        // Test PayPal config file
        if (file_exists('includes/paypal_config.php')) {
            require_once 'includes/paypal_config.php';
            addTestResult('✅ PayPal config file exists', 'pass');
            
            if (defined('PAYPAL_CLIENT_ID') && defined('PAYPAL_CLIENT_SECRET')) {
                addTestResult('✅ PayPal constants defined', 'pass');
            } else {
                addTestResult('❌ PayPal constants missing', 'fail');
            }
        } else {
            addTestResult('❌ PayPal config file missing', 'fail');
        }
        
        // Test PayPal service class
        if (file_exists('includes/PayPalService.php')) {
            require_once 'includes/PayPalService.php';
            addTestResult('✅ PayPalService class file exists', 'pass');
            
            if (class_exists('PayPalService')) {
                addTestResult('✅ PayPalService class loaded', 'pass');
            } else {
                addTestResult('❌ PayPalService class not found', 'fail');
            }
        } else {
            addTestResult('❌ PayPalService class file missing', 'fail');
        }
        
        // Test API endpoints
        $apiFiles = [
            'api/paypal_create_order.php' => 'PayPal order creation',
            'api/paypal_capture_order.php' => 'PayPal payment capture'
        ];
        
        foreach ($apiFiles as $file => $description) {
            if (file_exists($file)) {
                addTestResult("✅ $description endpoint exists", 'pass');
            } else {
                addTestResult("❌ $description endpoint missing", 'fail');
            }
        }
        
        // Test webhook handler
        if (file_exists('paypal_webhook.php')) {
            addTestResult('✅ PayPal webhook handler exists', 'pass');
        } else {
            addTestResult('❌ PayPal webhook handler missing', 'fail');
        }
        
    } catch (Exception $e) {
        addTestResult('❌ PayPal integration test error: ' . $e->getMessage(), 'fail');
    }
    echo "</div>";
}

function testAdminPanel() {
    global $testResults, $overallStatus;
    
    echo "<div class='test-section'>";
    echo "<h2>👨‍💼 Admin Panel Tests</h2>";
    
    // Test admin files exist
    $adminFiles = [
        'admin/index.php' => 'Admin dashboard',
        'admin/products.php' => 'Product management',
        'admin/orders.php' => 'Order management',
        'admin/users.php' => 'User management',
        'admin/add_product.php' => 'Add product form',
        'admin/edit_product.php' => 'Edit product form',
        'admin/view_order.php' => 'Order details view'
    ];
    
    foreach ($adminFiles as $file => $description) {
        if (file_exists($file)) {
            addTestResult("✅ $description exists", 'pass');
        } else {
            addTestResult("❌ $description missing", 'fail');
        }
    }
    
    // Test admin functions
    try {
        require_once 'includes/functions.php';
        
        if (function_exists('requireAdmin')) {
            addTestResult('✅ Admin authentication function exists', 'pass');
        } else {
            addTestResult('❌ Admin authentication function missing', 'fail');
        }
        
    } catch (Exception $e) {
        addTestResult('❌ Admin panel test error: ' . $e->getMessage(), 'fail');
    }
    echo "</div>";
}

function testFileUploadSystem() {
    global $testResults, $overallStatus;
    
    echo "<div class='test-section'>";
    echo "<h2>📁 File Upload System Tests</h2>";
    
    try {
        require_once 'includes/functions.php';
        
        // Test upload function exists
        if (function_exists('handleImageUpload')) {
            addTestResult('✅ Image upload function exists', 'pass');
        } else {
            addTestResult('❌ Image upload function missing', 'fail');
        }
        
        // Test upload directories
        $uploadDirs = ['uploads/products/', 'uploads/categories/', 'uploads/brands/'];
        foreach ($uploadDirs as $dir) {
            if (is_dir($dir)) {
                addTestResult("✅ Upload directory '$dir' exists", 'pass');
                if (is_writable($dir)) {
                    addTestResult("✅ Directory '$dir' is writable", 'pass');
                } else {
                    addTestResult("❌ Directory '$dir' not writable", 'fail');
                }
            } else {
                addTestResult("❌ Upload directory '$dir' missing", 'fail');
            }
        }
        
    } catch (Exception $e) {
        addTestResult('❌ File upload test error: ' . $e->getMessage(), 'fail');
    }
    echo "</div>";
}

function testSecurityFeatures() {
    global $testResults, $overallStatus;
    
    echo "<div class='test-section'>";
    echo "<h2>🛡️ Security Features Tests</h2>";
    
    try {
        require_once 'includes/functions.php';
        
        // Test security functions
        $securityFunctions = [
            'sanitizeInput', 'generateCSRFToken', 'validateCSRFToken',
            'hashPassword', 'verifyPassword'
        ];
        
        foreach ($securityFunctions as $function) {
            if (function_exists($function)) {
                addTestResult("✅ Security function '$function' exists", 'pass');
            } else {
                addTestResult("❌ Security function '$function' missing", 'fail');
            }
        }
        
        // Test input sanitization
        $testInput = '<script>alert("xss")</script>';
        $sanitized = sanitizeInput($testInput);
        if (strpos($sanitized, '<script>') === false) {
            addTestResult('✅ Input sanitization works', 'pass');
        } else {
            addTestResult('❌ Input sanitization failed', 'fail');
        }
        
        // Test CSRF token
        $token = generateCSRFToken();
        if (validateCSRFToken($token)) {
            addTestResult('✅ CSRF token validation works', 'pass');
        } else {
            addTestResult('❌ CSRF token validation failed', 'fail');
        }
        
    } catch (Exception $e) {
        addTestResult('❌ Security test error: ' . $e->getMessage(), 'fail');
    }
    echo "</div>";
}

function testFrontendFunctionality() {
    global $testResults, $overallStatus;
    
    echo "<div class='test-section'>";
    echo "<h2>🎨 Frontend Functionality Tests</h2>";
    
    // Test main pages exist
    $mainPages = [
        'index.php' => 'Homepage',
        'products.php' => 'Products page',
        'product.php' => 'Product details',
        'cart.php' => 'Shopping cart',
        'checkout.php' => 'Checkout',
        'login.php' => 'Login page',
        'register.php' => 'Registration',
        'about.php' => 'About page',
        'contact.php' => 'Contact page',
        'brands.php' => 'Brands page',
        'brand.php' => 'Brand page',
        'accessories.php' => 'Accessories page',
        'faq.php' => 'FAQ page',
        'shipping.php' => 'Shipping info',
        'returns.php' => 'Returns policy',
        'warranty.php' => 'Warranty info',
        'privacy.php' => 'Privacy policy',
        'terms.php' => 'Terms of service'
    ];
    
    foreach ($mainPages as $file => $description) {
        if (file_exists($file)) {
            addTestResult("✅ $description exists", 'pass');
        } else {
            addTestResult("❌ $description missing", 'fail');
        }
    }
    
    // Test CSS and JS files
    if (file_exists('css/style.css')) {
        addTestResult('✅ Main stylesheet exists', 'pass');
    } else {
        addTestResult('❌ Main stylesheet missing', 'fail');
    }
    
    if (file_exists('css/responsive.css')) {
        addTestResult('✅ Responsive stylesheet exists', 'pass');
    } else {
        addTestResult('❌ Responsive stylesheet missing', 'fail');
    }
    
    if (file_exists('js/main.js')) {
        addTestResult('✅ Main JavaScript file exists', 'pass');
    } else {
        addTestResult('❌ Main JavaScript file missing', 'fail');
    }
    
    echo "</div>";
}

function addTestResult($message, $status) {
    global $testResults, $overallStatus;
    
    $testResults[] = ['message' => $message, 'status' => $status];
    
    $class = 'test-info';
    switch ($status) {
        case 'pass':
            $class = 'test-pass';
            break;
        case 'fail':
            $class = 'test-fail';
            $overallStatus = false;
            break;
        case 'warning':
            $class = 'test-warning';
            break;
    }
    
    echo "<div class='test-result $class'>$message</div>";
}

function displayTestSummary() {
    global $testResults, $overallStatus;
    
    echo "<div class='test-section'>";
    echo "<h2>📊 Test Summary</h2>";
    
    $passed = count(array_filter($testResults, fn($r) => $r['status'] === 'pass'));
    $failed = count(array_filter($testResults, fn($r) => $r['status'] === 'fail'));
    $warnings = count(array_filter($testResults, fn($r) => $r['status'] === 'warning'));
    $total = count($testResults);
    
    $summaryClass = $overallStatus ? 'test-pass' : 'test-fail';
    $statusText = $overallStatus ? '✅ ALL TESTS PASSED' : '❌ SOME TESTS FAILED';
    
    echo "<div class='test-result $summaryClass'>";
    echo "<h3>$statusText</h3>";
    echo "<p><strong>Total Tests:</strong> $total</p>";
    echo "<p><strong>Passed:</strong> $passed</p>";
    echo "<p><strong>Failed:</strong> $failed</p>";
    echo "<p><strong>Warnings:</strong> $warnings</p>";
    echo "</div>";
    
    if ($overallStatus) {
        echo "<div class='summary'>";
        echo "<h3>🎉 Project Status: READY</h3>";
        echo "<p>Your outboard motors website is fully functional and ready for use!</p>";
        echo "<p><a href='index.php' style='background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin: 8px;'>🚀 Launch Website</a></p>";
        echo "<p><a href='admin/' style='background: #0ea5e9; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin: 8px;'>👨‍💼 Admin Panel</a></p>";
        echo "</div>";
    } else {
        echo "<div class='summary'>";
        echo "<h3>⚠️ Issues Found</h3>";
        echo "<p>Please fix the failed tests before launching the website.</p>";
        echo "</div>";
    }
    echo "</div>";
}
?>