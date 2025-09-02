<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Create demo customer account
$db = Database::getInstance();

try {
    // Check if demo customer already exists
    $existing = $db->fetchOne("SELECT id FROM users WHERE email = 'demo@example.com'");
    
    if (!$existing) {
        $db->insert('users', [
            'username' => 'demo@example.com',
            'email' => 'demo@example.com',
            'password' => hashPassword('demo123'),
            'first_name' => 'Demo',
            'last_name' => 'Customer',
            'role' => 'customer'
        ]);
        echo "Demo customer account created (demo@example.com / demo123)<br>";
    } else {
        echo "Demo customer account already exists<br>";
    }
    
    // Check admin account
    $adminExists = $db->fetchOne("SELECT id FROM users WHERE email = 'admin@outboard-sales.com'");
    if ($adminExists) {
        echo "Admin account exists (admin@outboard-sales.com / admin123)<br>";
    } else {
        echo "Admin account not found - check database.sql<br>";
    }
    
    echo "<br><strong>Your Outboard Motors website has been set up!</strong><br><br>";
    echo "<strong>Next Steps:</strong><br>";
    echo "1. Import database.sql into your MySQL database<br>";
    echo "2. Update database credentials in includes/config.php<br>";
    echo "3. Place your website files in your web server directory<br>";
    echo "4. Visit the site at your local server URL<br><br>";
    
    echo "<strong>Login Credentials:</strong><br>";
    echo "Admin: admin@outboard-sales.com / admin123<br>";
    echo "Demo Customer: demo@example.com / demo123<br><br>";
    
    echo "<strong>Features Included:</strong><br>";
    echo "• Product catalog with categories and brands<br>";
    echo "• Shopping cart functionality<br>";
    echo "• User registration and login<br>";
    echo "• Product search and filtering<br>";
    echo "• Admin panel for managing products<br>";
    echo "• Responsive design for mobile devices<br>";
    echo "• Sample outboard motor data<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
