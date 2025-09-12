<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

echo "<h2>Filter Debug Test</h2>";

try {
    $db = Database::getInstance();
    
    // Test database connection
    echo "<h3>1. Database Connection</h3>";
    echo "✓ Database connected successfully<br>";
    
    // Check if tables exist
    echo "<h3>2. Table Structure</h3>";
    $tables = ['products', 'categories', 'brands'];
    foreach ($tables as $table) {
        try {
            $count = $db->fetchColumn("SELECT COUNT(*) FROM $table");
            echo "✓ $table: $count records<br>";
        } catch (Exception $e) {
            echo "✗ $table: Error - " . $e->getMessage() . "<br>";
        }
    }
    
    // Test sample data
    echo "<h3>3. Sample Data</h3>";
    
    // Categories
    echo "<strong>Categories:</strong><br>";
    $categories = getAllCategories();
    if ($categories) {
        foreach ($categories as $cat) {
            echo "- ID: {$cat['id']}, Name: {$cat['name']}<br>";
        }
    } else {
        echo "No categories found<br>";
    }
    
    echo "<br><strong>Brands:</strong><br>";
    $brands = getAllBrands();
    if ($brands) {
        foreach ($brands as $brand) {
            echo "- ID: {$brand['id']}, Name: {$brand['name']}<br>";
        }
    } else {
        echo "No brands found<br>";
    }
    
    echo "<br><strong>Sample Products:</strong><br>";
    $products = $db->fetchAll("SELECT p.*, b.name as brand_name, c.name as category_name 
                               FROM products p 
                               LEFT JOIN brands b ON p.brand_id = b.id 
                               LEFT JOIN categories c ON p.category_id = c.id 
                               WHERE p.status = 'active' 
                               LIMIT 5");
    if ($products) {
        foreach ($products as $product) {
            $price = $product['sale_price'] ?: $product['price'];
            echo "- {$product['name']} | Brand: {$product['brand_name']} | Category: {$product['category_name']} | Price: \${$price}<br>";
        }
    } else {
        echo "No products found<br>";
    }
    
    // Test filter functionality
    echo "<h3>4. Test Filter Functions</h3>";
    
    // Test search with different parameters
    $testParams = [
        ['query' => null, 'category' => null, 'brand' => null, 'minPrice' => null, 'maxPrice' => null, 'desc' => 'All products'],
        ['query' => 'yamaha', 'category' => null, 'brand' => null, 'minPrice' => null, 'maxPrice' => null, 'desc' => 'Search: yamaha'],
        ['query' => null, 'category' => 1, 'brand' => null, 'minPrice' => null, 'maxPrice' => null, 'desc' => 'Category ID: 1'],
        ['query' => null, 'category' => null, 'brand' => 1, 'minPrice' => null, 'maxPrice' => null, 'desc' => 'Brand ID: 1'],
        ['query' => null, 'category' => null, 'brand' => null, 'minPrice' => 100, 'maxPrice' => 1000, 'desc' => 'Price range: $100-$1000'],
    ];
    
    foreach ($testParams as $params) {
        echo "<strong>{$params['desc']}:</strong> ";
        $results = searchProducts($params['query'], $params['category'], $params['brand'], $params['minPrice'], $params['maxPrice'], 10, 0);
        echo count($results) . " results<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>