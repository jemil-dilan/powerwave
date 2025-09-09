<?php
/**
 * Database Fix and Setup Script for Ubuntu Deployment
 * 
 * This script fixes database connection issues and ensures proper setup
 */

require_once 'includes/config.php';

echo "=== Database Fix and Setup for Ubuntu ===\n";
echo "This script will diagnose and fix database issues\n\n";

// Colors for CLI output
function colorOutput($text, $color = 'white') {
    $colors = [
        'red' => "\033[0;31m",
        'green' => "\033[0;32m", 
        'yellow' => "\033[1;33m",
        'blue' => "\033[0;34m",
        'white' => "\033[0;37m",
        'reset' => "\033[0m"
    ];
    
    return $colors[$color] . $text . $colors['reset'];
}

function printStatus($message, $success = true) {
    $symbol = $success ? '✓' : '✗';
    $color = $success ? 'green' : 'red';
    echo colorOutput("[$symbol] $message", $color) . "\n";
}

function printInfo($message) {
    echo colorOutput("[i] $message", 'yellow') . "\n";
}

// Test 1: Check database connection
echo "1. Testing database connection...\n";

try {
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    printStatus("Database server connection successful");
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    if ($stmt->rowCount() > 0) {
        printStatus("Database '" . DB_NAME . "' exists");
    } else {
        printInfo("Database '" . DB_NAME . "' doesn't exist, creating...");
        $pdo->exec("CREATE DATABASE `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        printStatus("Database '" . DB_NAME . "' created successfully");
    }
    
    // Connect to the specific database
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    printStatus("Connected to database '" . DB_NAME . "'");
    
} catch (PDOException $e) {
    printStatus("Database connection failed: " . $e->getMessage(), false);
    echo "\nUbuntu Database Setup Commands:\n";
    echo "1. Install MySQL: sudo apt update && sudo apt install mysql-server\n";
    echo "2. Secure MySQL: sudo mysql_secure_installation\n";
    echo "3. Login to MySQL: sudo mysql -u root -p\n";
    echo "4. Create database: CREATE DATABASE " . DB_NAME . ";\n";
    echo "5. Create user: CREATE USER '" . DB_USER . "'@'localhost' IDENTIFIED BY 'your_password';\n";
    echo "6. Grant privileges: GRANT ALL PRIVILEGES ON " . DB_NAME . ".* TO '" . DB_USER . "'@'localhost';\n";
    echo "7. Flush privileges: FLUSH PRIVILEGES;\n";
    exit(1);
}

// Test 2: Check required tables
echo "\n2. Checking database tables...\n";

$requiredTables = [
    'brands' => "CREATE TABLE brands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        logo VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    'categories' => "CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    'products' => "CREATE TABLE products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        model VARCHAR(100) NOT NULL,
        brand_id INT NOT NULL,
        category_id INT NOT NULL,
        price VARCHAR(50) NOT NULL,
        sale_price VARCHAR(50) NULL,
        description TEXT,
        specifications TEXT,
        horsepower INT NOT NULL,
        stroke ENUM('2-stroke', '4-stroke') DEFAULT '4-stroke',
        fuel_type ENUM('gasoline', 'diesel', 'electric') DEFAULT 'gasoline',
        shaft_length ENUM('short', 'long', 'extra-long') DEFAULT 'long',
        weight DECIMAL(8,2) NULL,
        displacement DECIMAL(8,2) NULL,
        cylinders INT NULL,
        cooling_system ENUM('water-cooled', 'air-cooled') DEFAULT 'water-cooled',
        starting_system ENUM('manual', 'electric', 'both') DEFAULT 'manual',
        stock_quantity INT DEFAULT 0,
        min_stock_level INT DEFAULT 5,
        sku VARCHAR(50) NOT NULL UNIQUE,
        status ENUM('active', 'inactive', 'discontinued') DEFAULT 'active',
        featured TINYINT(1) DEFAULT 0,
        main_image VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (brand_id) REFERENCES brands(id),
        FOREIGN KEY (category_id) REFERENCES categories(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    'users' => "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        role ENUM('customer', 'admin') DEFAULT 'customer',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    'orders' => "CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        order_number VARCHAR(50) NOT NULL UNIQUE,
        status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
        payment_method VARCHAR(50),
        payment_reference VARCHAR(255),
        subtotal DECIMAL(10,2) NOT NULL,
        tax_amount DECIMAL(10,2) NOT NULL,
        shipping_amount DECIMAL(10,2) NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        billing_first_name VARCHAR(100) NOT NULL,
        billing_last_name VARCHAR(100) NOT NULL,
        billing_email VARCHAR(255) NOT NULL,
        billing_phone VARCHAR(20),
        billing_address VARCHAR(255) NOT NULL,
        billing_city VARCHAR(100) NOT NULL,
        billing_state VARCHAR(100) NOT NULL,
        billing_zip VARCHAR(20) NOT NULL,
        billing_country VARCHAR(100) NOT NULL,
        shipping_first_name VARCHAR(100),
        shipping_last_name VARCHAR(100),
        shipping_address VARCHAR(255),
        shipping_city VARCHAR(100),
        shipping_state VARCHAR(100),
        shipping_zip VARCHAR(20),
        shipping_country VARCHAR(100),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    'cart' => "CREATE TABLE cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        session_id VARCHAR(128) NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($requiredTables as $tableName => $createSql) {
    $stmt = $pdo->query("SHOW TABLES LIKE '$tableName'");
    if ($stmt->rowCount() > 0) {
        printStatus("Table '$tableName' exists");
    } else {
        printInfo("Creating table '$tableName'...");
        try {
            $pdo->exec($createSql);
            printStatus("Table '$tableName' created successfully");
        } catch (PDOException $e) {
            printStatus("Failed to create table '$tableName': " . $e->getMessage(), false);
        }
    }
}

// Test 3: Insert sample data if tables are empty
echo "\n3. Checking for sample data...\n";

// Check and insert brands
$stmt = $pdo->query("SELECT COUNT(*) as count FROM brands");
$brandCount = $stmt->fetch()['count'];

if ($brandCount == 0) {
    printInfo("Inserting sample brands...");
    $brands = [
        ['Yamaha', 'Leading manufacturer of marine outboard motors'],
        ['Mercury', 'High-performance outboard motors'],
        ['Honda', 'Reliable and fuel-efficient outboard motors'],
        ['Suzuki', 'Innovative outboard motor technology'],
        ['Evinrude', 'Powerful two-stroke outboard motors'],
        ['Tohatsu', 'Dependable outboard motors for all applications']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO brands (name, description) VALUES (?, ?)");
    foreach ($brands as $brand) {
        $stmt->execute($brand);
    }
    printStatus("Sample brands inserted");
} else {
    printStatus("Brands table has data ($brandCount brands)");
}

// Check and insert categories  
$stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
$categoryCount = $stmt->fetch()['count'];

if ($categoryCount == 0) {
    printInfo("Inserting sample categories...");
    $categories = [
        ['2-Stroke Outboards', '2-stroke outboard motors for maximum power'],
        ['4-Stroke Outboards', '4-stroke outboard motors for fuel efficiency'],
        ['Small Outboards', 'Portable outboard motors under 20HP']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
    printStatus("Sample categories inserted");
} else {
    printStatus("Categories table has data ($categoryCount categories)");
}

// Test 4: Test Database class
echo "\n4. Testing Database class...\n";

try {
    require_once 'includes/database.php';
    $db = Database::getInstance();
    
    $brands = $db->fetchAll("SELECT * FROM brands LIMIT 3");
    printStatus("Database class working correctly (" . count($brands) . " brands fetched)");
    
    $categories = $db->fetchAll("SELECT * FROM categories LIMIT 3");  
    printStatus("Database queries working correctly (" . count($categories) . " categories fetched)");
    
} catch (Exception $e) {
    printStatus("Database class error: " . $e->getMessage(), false);
}

// Test 5: Check upload directories
echo "\n5. Checking upload directories...\n";

$uploadDirs = ['uploads/', 'uploads/products/', 'uploads/brands/', 'uploads/categories/'];

foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        printInfo("Creating directory: $dir");
        if (mkdir($dir, 0775, true)) {
            printStatus("Directory created: $dir");
        } else {
            printStatus("Failed to create directory: $dir", false);
        }
    } else {
        printStatus("Directory exists: $dir");
    }
    
    if (is_writable($dir)) {
        printStatus("Directory writable: $dir");
    } else {
        printStatus("Directory not writable: $dir (run: sudo chmod -R 775 uploads/)", false);
    }
}

echo "\n" . colorOutput("=== Database Setup Complete ===", 'green') . "\n";
echo "\nFor Ubuntu deployment, remember to:\n";
echo "1. Set proper file permissions: sudo chown -R www-data:www-data .\n";
echo "2. Set upload permissions: sudo chmod -R 775 uploads/\n";
echo "3. Restart Apache: sudo systemctl restart apache2\n";
echo "4. Check error logs: sudo tail -f /var/log/apache2/error.log\n";
?>
