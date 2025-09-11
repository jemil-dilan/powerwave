<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

try {
    $db = Database::getInstance();
    
    // Check if admin exists
    $existing = $db->fetchOne("SELECT id FROM users WHERE email = 'admin@outboard-sales.com'");
    
    if ($existing) {
        // Update existing admin password
        $newPassword = hashPassword('admin123');
        $db->update('users', 
            ['password' => $newPassword], 
            'email = ?', 
            ['admin@outboard-sales.com']
        );
        echo "Admin password updated successfully!<br>";
    } else {
        // Create new admin with correct password hash
        $db->insert('users', [
            'username' => 'admin',
            'email' => 'admin@outboard-sales.com',
            'password' => '$2y$10$hPH6OTrERg5vR9z4aM9B7Okz4LNnEO8PQLYnU0I1.YjA4RcplpMse',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'role' => 'admin'
        ]);
        echo "Admin account created successfully!<br>";
    }
    
    echo "<br><strong>Admin Login Credentials:</strong><br>";
    echo "Email: admin@outboard-sales.com<br>";
    echo "Password: admin123<br><br>";
    
    echo "You can now access the admin panel at: <a href='admin/'>admin/</a><br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
