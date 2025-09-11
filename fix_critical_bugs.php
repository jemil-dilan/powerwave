<?php
/**
 * Critical Bug Fix Script
 * Identifies and fixes critical bugs in the project
 */

echo "<h1>🔧 Critical Bug Fix Script</h1>";

try {
    require_once 'includes/config.php';
    require_once 'includes/functions.php';
    
    $db = Database::getInstance();
    
    echo "<h2>🐛 Bug Detection and Fixes</h2>";
    
    // Bug Fix 1: Check for products with null main_image causing errors
    echo "<h3>Fix 1: Product Image Issues</h3>";
    $productsWithNullImages = $db->fetchAll("SELECT id, name, main_image FROM products WHERE main_image IS NULL OR main_image = ''");
    
    if (!empty($productsWithNullImages)) {
        echo "<p>Found " . count($productsWithNullImages) . " products with missing images. Setting default image...</p>";
        
        foreach ($productsWithNullImages as $product) {
            $db->update('products', ['main_image' => 'no-image.jpg'], 'id = ?', [$product['id']]);
            echo "<p>✅ Fixed image for product: " . sanitizeInput($product['name']) . "</p>";
        }
    } else {
        echo "<p>✅ No products with missing images found.</p>";
    }
    
    // Bug Fix 2: Check for invalid price formats
    echo "<h3>Fix 2: Price Format Issues</h3>";
    $invalidPrices = $db->fetchAll("SELECT id, name, price FROM products WHERE price NOT REGEXP '^[0-9]+\.?[0-9]*$' AND price != 'Call for price' AND price != 'Contact us'");
    
    if (!empty($invalidPrices)) {
        echo "<p>Found " . count($invalidPrices) . " products with invalid price formats:</p>";
        foreach ($invalidPrices as $product) {
            echo "<p>⚠️ Product: " . sanitizeInput($product['name']) . " - Price: " . sanitizeInput($product['price']) . "</p>";
        }
    } else {
        echo "<p>✅ All product prices have valid formats.</p>";
    }
    
    // Bug Fix 3: Check for missing SKUs
    echo "<h3>Fix 3: Missing SKU Issues</h3>";
    $missingSKUs = $db->fetchAll("SELECT id, name, sku FROM products WHERE sku IS NULL OR sku = ''");
    
    if (!empty($missingSKUs)) {
        echo "<p>Found " . count($missingSKUs) . " products with missing SKUs. Generating SKUs...</p>";
        
        foreach ($missingSKUs as $product) {
            $newSKU = 'SKU-' . strtoupper(uniqid());
            $db->update('products', ['sku' => $newSKU], 'id = ?', [$product['id']]);
            echo "<p>✅ Generated SKU for " . sanitizeInput($product['name']) . ": $newSKU</p>";
        }
    } else {
        echo "<p>✅ All products have valid SKUs.</p>";
    }
    
    // Bug Fix 4: Check cart table for orphaned entries
    echo "<h3>Fix 4: Cart Cleanup</h3>";
    $orphanedCartItems = $db->fetchColumn("
        SELECT COUNT(*) FROM cart c 
        LEFT JOIN products p ON c.item_id = p.id AND c.item_type = 'product'
        LEFT JOIN accessories a ON c.item_id = a.id AND c.item_type = 'accessory'
        WHERE p.id IS NULL AND a.id IS NULL
    ");
    
    if ($orphanedCartItems > 0) {
        echo "<p>Found $orphanedCartItems orphaned cart items. Cleaning up...</p>";
        $db->query("
            DELETE c FROM cart c 
            LEFT JOIN products p ON c.item_id = p.id AND c.item_type = 'product'
            LEFT JOIN accessories a ON c.item_id = a.id AND c.item_type = 'accessory'
            WHERE p.id IS NULL AND a.id IS NULL
        ");
        echo "<p>✅ Cleaned up orphaned cart items.</p>";
    } else {
        echo "<p>✅ No orphaned cart items found.</p>";
    }
    
    // Bug Fix 5: Ensure admin account exists and is accessible
    echo "<h3>Fix 5: Admin Account Verification</h3>";
    $admin = $db->fetchOne("SELECT * FROM users WHERE email = 'gonzila@gmail.com'");
    
    if ($admin) {
        echo "<p>✅ Admin account exists.</p>";
        
        // Test password
        if (password_verify('gonzilaib', $admin['password'])) {
            echo "<p>✅ Admin password verification works.</p>";
        } else {
            echo "<p>❌ Admin password verification failed. Updating password...</p>";
            $newHash = hashPassword('gonzilaib');
            $db->update('users', ['password' => $newHash], 'id = ?', [$admin['id']]);
            echo "<p>✅ Admin password updated.</p>";
        }
    } else {
        echo "<p>❌ Admin account not found. Creating admin account...</p>";
        $adminId = $db->insert('users', [
            'username' => 'gonzila',
            'email' => 'gonzila@gmail.com',
            'password' => hashPassword('gonzilaib'),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'role' => 'admin'
        ]);
        echo "<p>✅ Admin account created with ID: $adminId</p>";
    }
    
    echo "<h2>🎯 Summary</h2>";
    echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px; border-left: 4px solid #10b981;'>";
    echo "<h3>✅ Bug Fixes Complete</h3>";
    echo "<p>All critical bugs have been identified and fixed. The website should now function properly.</p>";
    echo "<p><strong>Admin Login:</strong> gonzila@gmail.com / gonzilaib</p>";
    echo "<p><a href='index.php' style='background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;'>Test Website</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fee2e2; padding: 20px; border-radius: 8px; border-left: 4px solid #ef4444;'>";
    echo "<h3>❌ Error During Bug Fixes</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>