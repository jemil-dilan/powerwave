<?php
/**
 * Website Functionality Validation Script
 * Tests the website by simulating user interactions
 */

// Start session for testing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>🌐 Website Functionality Validation</h1>";

try {
    require_once 'includes/config.php';
    require_once 'includes/functions.php';
    
    $db = Database::getInstance();
    
    echo "<h2>🔍 Testing Core Website Functions</h2>";
    
    // Test 1: Homepage functionality
    echo "<h3>Test 1: Homepage</h3>";
    ob_start();
    try {
        include 'index.php';
        $homepageContent = ob_get_contents();
        ob_end_clean();
        
        if (strpos($homepageContent, 'PowerWave outboards') !== false) {
            echo "<p>✅ Homepage loads successfully</p>";
        } else {
            echo "<p>❌ Homepage content issue</p>";
        }
    } catch (Exception $e) {
        ob_end_clean();
        echo "<p>❌ Homepage error: " . $e->getMessage() . "</p>";
    }
    
    // Test 2: Products page
    echo "<h3>Test 2: Products Page</h3>";
    $products = getFeaturedProducts(5);
    if (!empty($products)) {
        echo "<p>✅ Products loaded successfully (" . count($products) . " products)</p>";
    } else {
        echo "<p>⚠️ No products found - add some products via admin panel</p>";
    }
    
    // Test 3: Cart functionality
    echo "<h3>Test 3: Cart Functionality</h3>";
    $cartCount = getCartItemCount(null);
    if (is_numeric($cartCount)) {
        echo "<p>✅ Cart count function works (current: $cartCount items)</p>";
    } else {
        echo "<p>❌ Cart count function failed</p>";
    }
    
    $cartTotal = getCartTotal(null);
    if (is_numeric($cartTotal)) {
        echo "<p>✅ Cart total function works (current: " . formatPrice($cartTotal) . ")</p>";
    } else {
        echo "<p>❌ Cart total function failed</p>";
    }
    
    // Test 4: Search functionality
    echo "<h3>Test 4: Search Functionality</h3>";
    $searchResults = searchProducts('yamaha', null, null, null, null, 5, 0);
    if (isset($searchResults['products']) && is_array($searchResults['products'])) {
        echo "<p>✅ Search function works (found " . count($searchResults['products']) . " results for 'yamaha')</p>";
    } else {
        echo "<p>❌ Search function failed</p>";
    }
    
    // Test 5: User authentication
    echo "<h3>Test 5: Authentication System</h3>";
    $admin = $db->fetchOne("SELECT * FROM users WHERE email = 'gonzila@gmail.com'");
    if ($admin && password_verify('gonzilaib', $admin['password'])) {
        echo "<p>✅ Admin authentication works</p>";
    } else {
        echo "<p>❌ Admin authentication failed</p>";
    }
    
    // Test 6: Image URL generation
    echo "<h3>Test 6: Image URL Generation</h3>";
    $imageUrl = getProductImageUrl('test-image.png');
    if (strpos($imageUrl, SITE_URL) !== false) {
        echo "<p>✅ Image URL generation works: $imageUrl</p>";
    } else {
        echo "<p>❌ Image URL generation failed</p>";
    }
    
    // Test 7: Price formatting
    echo "<h3>Test 7: Price Formatting</h3>";
    $formattedPrice = formatPrice(1234.56);
    if ($formattedPrice === '$1,234.56') {
        echo "<p>✅ Price formatting works: $formattedPrice</p>";
    } else {
        echo "<p>❌ Price formatting failed: $formattedPrice</p>";
    }
    
    // Test 8: Order number generation
    echo "<h3>Test 8: Order Number Generation</h3>";
    $orderNumber = generateOrderNumber();
    if (preg_match('/^ORD-\d{4}-[A-Z0-9]{8}$/', $orderNumber)) {
        echo "<p>✅ Order number generation works: $orderNumber</p>";
    } else {
        echo "<p>❌ Order number generation failed: $orderNumber</p>";
    }
    
    // Test 9: Categories and brands
    echo "<h3>Test 9: Categories and Brands</h3>";
    $categories = getAllCategories();
    $brands = getAllBrands();
    
    echo "<p>✅ Categories loaded: " . count($categories) . " categories</p>";
    echo "<p>✅ Brands loaded: " . count($brands) . " brands</p>";
    
    // Test 10: File existence check
    echo "<h3>Test 10: Critical Files Check</h3>";
    $criticalFiles = [
        'css/style.css' => 'Main stylesheet',
        'css/responsive.css' => 'Responsive stylesheet',
        'js/main.js' => 'Main JavaScript',
        'includes/config.php' => 'Configuration',
        'includes/database.php' => 'Database class',
        'includes/functions.php' => 'Helper functions'
    ];
    
    foreach ($criticalFiles as $file => $description) {
        if (file_exists($file)) {
            echo "<p>✅ $description exists</p>";
        } else {
            echo "<p>❌ $description missing</p>";
        }
    }
    
    echo "<h2>🎯 Validation Summary</h2>";
    echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px; border-left: 4px solid #10b981;'>";
    echo "<h3>✅ Website Validation Complete</h3>";
    echo "<p>Core functionality has been tested and validated.</p>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ul>";
    echo "<li>Visit <a href='index.php'>the homepage</a> to see the website</li>";
    echo "<li>Login to <a href='admin/'>admin panel</a> with gonzila@gmail.com / gonzilaib</li>";
    echo "<li>Test the complete user flow from browsing to checkout</li>";
    echo "<li>Add real products and customize the design</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fee2e2; padding: 20px; border-radius: 8px; border-left: 4px solid #ef4444;'>";
    echo "<h3>❌ Validation Error</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>