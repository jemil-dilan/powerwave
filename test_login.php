<?php
// Simple test to verify password hashing works
$password = 'gonzilaib';
$hash = '$2y$10$f1mjxKljo3i2zBwNaePEB.CulIWS0PVOAkIlLucYPWnJoTRaGYOx6';

echo "<h2>Password Test</h2>";
echo "<p><strong>Password:</strong> $password</p>";
echo "<p><strong>Hash:</strong> $hash</p>";

$verify = password_verify($password, $hash);
echo "<p><strong>Verification:</strong> " . ($verify ? '✅ SUCCESS' : '❌ FAILED') . "</p>";

// Test current hash generation
$newHash = password_hash($password, PASSWORD_DEFAULT);
echo "<p><strong>New Hash:</strong> $newHash</p>";

$verifyNew = password_verify($password, $newHash);
echo "<p><strong>New Verification:</strong> " . ($verifyNew ? '✅ SUCCESS' : '❌ FAILED') . "</p>";

// Test database connection
try {
    require_once 'includes/config.php';
    require_once 'includes/functions.php';
    $db = Database::getInstance();
    echo "<p><strong>Database:</strong> ✅ Connected</p>";
    
    // Check if admin exists
    $admin = $db->fetchOne("SELECT * FROM users WHERE email = 'gonzila@gmail.com'");
    if ($admin) {
        echo "<p><strong>Admin Found:</strong> ✅ YES</p>";
        echo "<p><strong>Admin Role:</strong> " . $admin['role'] . "</p>";
        $dbVerify = password_verify($password, $admin['password']);
        echo "<p><strong>DB Password Check:</strong> " . ($dbVerify ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
    } else {
        echo "<p><strong>Admin Found:</strong> ❌ NO</p>";
    }
} catch (Exception $e) {
    echo "<p><strong>Database:</strong> ❌ Error: " . $e->getMessage() . "</p>";
}
?>
