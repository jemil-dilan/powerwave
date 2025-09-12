<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

echo "<h2>Admin Setup</h2>";

try {
    $db = Database::getInstance();
    
    // Delete any existing admin accounts to avoid conflicts
    $db->query("DELETE FROM users WHERE role = 'admin'");
    echo "Cleared existing admin accounts...<br>";
    
    // Create your admin account
    $adminData = [
        'username' => 'gonzila',
        'email' => 'gonzila@gmail.com',
        'password' => '$2y$10$f1mjxKljo3i2zBwNaePEB.CulIWS0PVOAkIlLucYPWnJoTRaGYOx6', // gonzilaib
        'first_name' => 'Admin',
        'last_name' => 'User',
        'role' => 'admin'
    ];
    
    $adminId = $db->insert('users', $adminData);
    
    echo "<div style='background: #dcfce7; color: #059669; padding: 16px; border-radius: 8px; margin: 16px 0;'>";
    echo "<h3>✅ Admin Account Created Successfully!</h3>";
    echo "<p><strong>Email:</strong> gonzila@gmail.com</p>";
    echo "<p><strong>Password:</strong> gonzilaib</p>";
    echo "<p><strong>Admin ID:</strong> $adminId</p>";
    echo "</div>";
    
    // Test the password verification
    $testUser = $db->fetchOne("SELECT * FROM users WHERE email = 'gonzila@gmail.com'");
    if ($testUser) {
        $passwordWorks = password_verify('gonzilaib', $testUser['password']);
        echo "<div style='background: " . ($passwordWorks ? '#dcfce7; color: #059669' : '#fee2e2; color: #dc2626') . "; padding: 16px; border-radius: 8px; margin: 16px 0;'>";
        echo "<h4>" . ($passwordWorks ? '✅ Password Verification: WORKING' : '❌ Password Verification: FAILED') . "</h4>";
        echo "</div>";
    }
    
    echo "<div style='background: #e0f2fe; color: #0369a1; padding: 16px; border-radius: 8px; margin: 16px 0;'>";
    echo "<h4>🚀 Next Steps:</h4>";
    echo "<ol>";
    echo "<li>Go to <a href='login.php'>login.php</a></li>";
    echo "<li>Enter email: <strong>gonzila@gmail.com</strong></li>";
    echo "<li>Enter password: <strong>gonzilaib</strong></li>";
    echo "<li>Access admin panel at <a href='admin/'>admin/</a></li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fee2e2; color: #dc2626; padding: 16px; border-radius: 8px;'>";
    echo "<h3>❌ Error: " . $e->getMessage() . "</h3>";
    echo "</div>";
}
?>
