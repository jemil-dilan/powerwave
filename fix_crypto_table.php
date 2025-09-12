<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

try {
    $db = Database::getInstance();
    
    // Create the coinbase_charges table
    $sql = "
    CREATE TABLE IF NOT EXISTS coinbase_charges (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        charge_code VARCHAR(50) NOT NULL,
        status VARCHAR(50) NOT NULL,
        amount DECIMAL(18, 8) NOT NULL,
        currency VARCHAR(10) NOT NULL,
        hosted_url TEXT,
        created_at TIMESTAMP NOT NULL,
        expires_at TIMESTAMP NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_order_id (order_id),
        INDEX idx_charge_code (charge_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $db->query($sql);
    
    echo "✅ SUCCESS: coinbase_charges table created successfully!\n";
    
    // Verify table exists
    $tables = $db->fetchAll("SHOW TABLES LIKE 'coinbase_charges'");
    if (count($tables) > 0) {
        echo "✅ VERIFIED: Table exists in database\n";
        
        // Show table structure
        $columns = $db->fetchAll("DESCRIBE coinbase_charges");
        echo "\n📋 Table structure:\n";
        foreach ($columns as $column) {
            echo "  - {$column['Field']}: {$column['Type']} {$column['Null']} {$column['Key']}\n";
        }
    } else {
        echo "❌ ERROR: Table was not created properly\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>