<?php
/**
 * Complete Setup Script for Outboard Motors Website with PayPal Integration
 * This script will help you set up the entire website including PayPal
 */

// Prevent session headers issue
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$setupSteps = [];
$currentStep = isset($_GET['step']) ? (int)$_GET['step'] : 1;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Outboard Motors Website</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f8fafc; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 32px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #0ea5e9; border-bottom: 3px solid #0ea5e9; padding-bottom: 10px; }
        h2 { color: #1f2937; margin-top: 30px; }
        .step { background: #f1f5f9; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0ea5e9; }
        .success { background: #d1fae5; border-left-color: #10b981; }
        .error { background: #fee2e2; border-left-color: #ef4444; }
        .warning { background: #fef3c7; border-left-color: #f59e0b; }
        .code { background: #1f2937; color: #e5e7eb; padding: 15px; border-radius: 6px; font-family: monospace; margin: 10px 0; overflow-x: auto; }
        .btn { display: inline-block; background: #0ea5e9; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 10px 5px; border: none; cursor: pointer; }
        .btn:hover { background: #0284c7; }
        .btn-success { background: #10b981; }
        .btn-warning { background: #f59e0b; }
        .progress { width: 100%; background: #e5e7eb; border-radius: 10px; overflow: hidden; margin: 20px 0; }
        .progress-bar { height: 20px; background: #0ea5e9; transition: width 0.3s; }
        ul li { margin: 8px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Outboard Motors Website Setup</h1>
        <p>Complete setup wizard for your outboard motors e-commerce website with PayPal integration.</p>
        
        <?php
        // Setup progress
        $totalSteps = 6;
        $progress = ($currentStep / $totalSteps) * 100;
        
        echo "<div class='progress'>";
        echo "<div class='progress-bar' style='width: {$progress}%'></div>";
        echo "</div>";
        echo "<p><strong>Step {$currentStep} of {$totalSteps}</strong></p>";
        
        switch ($currentStep) {
            case 1:
                setupStep1();
                break;
            case 2:
                setupStep2();
                break;
            case 3:
                setupStep3();
                break;
            case 4:
                setupStep4();
                break;
            case 5:
                setupStep5();
                break;
            case 6:
                setupStep6();
                break;
            default:
                setupComplete();
        }
        ?>
    </div>
</body>
</html>

<?php

function setupStep1() {
    echo "<h2>📋 Step 1: Project Overview</h2>";
    echo "<div class='step'>";
    echo "<h3>What's Included</h3>";
    echo "<ul>";
    echo "<li>✅ Complete PHP e-commerce website</li>";
    echo "<li>✅ Modern PayPal integration with JavaScript SDK v2</li>";
    echo "<li>✅ Admin panel for managing products and orders</li>";
    echo "<li>✅ Responsive design for mobile devices</li>";
    echo "<li>✅ Security features and best practices</li>";
    echo "<li>✅ Comprehensive test suite</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='step warning'>";
    echo "<h3>⚠️ Requirements</h3>";
    echo "<ul>";
    echo "<li>PHP 7.4 or higher with PDO extension</li>";
    echo "<li>MySQL 5.7 or higher</li>";
    echo "<li>Web server (Apache/Nginx/IIS)</li>";
    echo "<li>PayPal Developer Account</li>";
    echo "<li>SSL certificate (for production)</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<a href='?step=2' class='btn'>Continue to Database Setup →</a>";
}

function setupStep2() {
    echo "<h2>🗄️ Step 2: Database Configuration</h2>";
    
    // Test database connection
    try {
        require_once 'includes/config.php';
        require_once 'includes/functions.php';
        
        $db = Database::getInstance();
        
        echo "<div class='step success'>";
        echo "<h3>✅ Database Connection Successful</h3>";
        echo "<p>Connected to database: <strong>" . DB_NAME . "</strong></p>";
        echo "</div>";
        
        // Check if tables exist
        $tables = $db->fetchAll("SHOW TABLES");
        $tableNames = array_column($tables, 'Tables_in_' . DB_NAME);
        
        $requiredTables = ['users', 'products', 'categories', 'brands', 'orders', 'order_items', 'cart'];
        $missingTables = array_diff($requiredTables, $tableNames);
        
        if (empty($missingTables)) {
            echo "<div class='step success'>";
            echo "<h3>✅ Database Tables Found</h3>";
            echo "<p>All required tables are present.</p>";
            echo "</div>";
            
            // Check for PayPal table
            if (in_array('paypal_transactions', $tableNames)) {
                echo "<div class='step success'>";
                echo "<h3>✅ PayPal Tables Ready</h3>";
                echo "<p>PayPal transactions table is configured.</p>";
                echo "</div>";
            } else {
                echo "<div class='step warning'>";
                echo "<h3>⚠️ PayPal Table Missing</h3>";
                echo "<p>PayPal transactions table not found. You may need to run the updated database.sql file.</p>";
                echo "</div>";
            }
        } else {
            echo "<div class='step error'>";
            echo "<h3>❌ Missing Database Tables</h3>";
            echo "<p>Please import the database.sql file first:</p>";
            echo "<div class='code'>mysql -u root -p outboard_sales2 < database.sql</div>";
            echo "<p>Missing tables: " . implode(', ', $missingTables) . "</p>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='step error'>";
        echo "<h3>❌ Database Connection Failed</h3>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "<p>Please check your database configuration in <strong>includes/config.php</strong></p>";
        echo "</div>";
    }
    
    echo "<a href='?step=1' class='btn'>← Previous</a>";
    echo "<a href='?step=3' class='btn'>Continue to PayPal Setup →</a>";
}

function setupStep3() {
    echo "<h2>💳 Step 3: PayPal Configuration</h2>";
    
    try {
        require_once 'includes/config.php';
        require_once 'includes/paypal_config.php';
        
        // Check PayPal configuration
        $paypalConfigured = !in_array(PAYPAL_CLIENT_ID, ['YOUR_PAYPAL_CLIENT_ID', '{{PAYPAL_CLIENT_ID}}']);
        
        if ($paypalConfigured) {
            echo "<div class='step success'>";
            echo "<h3>✅ PayPal Credentials Configured</h3>";
            echo "<p>Environment: <strong>" . PAYPAL_ENVIRONMENT . "</strong></p>";
            echo "<p>Client ID: <strong>" . substr(PAYPAL_CLIENT_ID, 0, 10) . "...</strong></p>";
            echo "</div>";
        } else {
            echo "<div class='step warning'>";
            echo "<h3>⚠️ PayPal Credentials Not Set</h3>";
            echo "<p>You need to update your PayPal credentials in <strong>includes/paypal_config.php</strong></p>";
            echo "</div>";
        }
        
        // Check if PayPal service class exists
        if (file_exists('includes/PayPalService.php')) {
            echo "<div class='step success'>";
            echo "<h3>✅ PayPal Service Class Available</h3>";
            echo "<p>Modern PayPal integration is ready.</p>";
            echo "</div>";
        }
        
        // Check API endpoints
        if (file_exists('api/paypal_create_order.php') && file_exists('api/paypal_capture_order.php')) {
            echo "<div class='step success'>";
            echo "<h3>✅ PayPal API Endpoints Ready</h3>";
            echo "<p>Payment processing endpoints are configured.</p>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='step error'>";
        echo "<h3>❌ PayPal Configuration Error</h3>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
    
    echo "<div class='step'>";
    echo "<h3>📝 PayPal Setup Instructions</h3>";
    echo "<ol>";
    echo "<li>Go to <a href='https://developer.paypal.com' target='_blank'>PayPal Developer Portal</a></li>";
    echo "<li>Create or log into your developer account</li>";
    echo "<li>Create a new application</li>";
    echo "<li>Copy your Client ID and Client Secret</li>";
    echo "<li>Update <strong>includes/paypal_config.php</strong> with your credentials</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<a href='?step=2' class='btn'>← Previous</a>";
    echo "<a href='?step=4' class='btn'>Continue to Testing →</a>";
}

function setupStep4() {
    echo "<h2>🧪 Step 4: Run Tests</h2>";
    
    echo "<div class='step'>";
    echo "<h3>Test Your Installation</h3>";
    echo "<p>Click the button below to run comprehensive tests on your installation:</p>";
    echo "<a href='tests/paypal_tests.php' target='_blank' class='btn btn-success'>Run PayPal Tests</a>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Manual Testing</h3>";
    echo "<ol>";
    echo "<li>Test login with admin account: gonzila@gmail.com / gonzilaib</li>";
    echo "<li>Add products to cart</li>";
    echo "<li>Go through checkout process</li>";
    echo "<li>Test PayPal payment (sandbox mode)</li>";
    echo "<li>Verify order creation in admin panel</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Quick Tests</h3>";
    echo "<a href='test_login.php' target='_blank' class='btn'>Test Login System</a>";
    echo "<a href='admin/test_product_image.php' target='_blank' class='btn'>Test Image Upload</a>";
    echo "</div>";
    
    echo "<a href='?step=3' class='btn'>← Previous</a>";
    echo "<a href='?step=5' class='btn'>Continue to Final Setup →</a>";
}

function setupStep5() {
    echo "<h2>⚙️ Step 5: Final Configuration</h2>";
    
    echo "<div class='step'>";
    echo "<h3>Files to Customize</h3>";
    echo "<ul>";
    echo "<li><strong>includes/config.php</strong> - Update site name, email, database credentials</li>";
    echo "<li><strong>includes/paypal_config.php</strong> - Add your PayPal credentials</li>";
    echo "<li><strong>css/style.css</strong> - Customize colors and branding</li>";
    echo "<li><strong>images/</strong> - Replace logo and hero images</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='step warning'>";
    echo "<h3>🔐 Security Checklist</h3>";
    echo "<ul>";
    echo "<li>Change default admin password</li>";
    echo "<li>Update database credentials</li>";
    echo "<li>Disable error display for production</li>";
    echo "<li>Set up HTTPS/SSL certificate</li>";
    echo "<li>Configure proper file permissions</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>🎨 Branding Customization</h3>";
    echo "<ul>";
    echo "<li>Replace logo1.png with your logo</li>";
    echo "<li>Update site name in config.php</li>";
    echo "<li>Customize colors in CSS files</li>";
    echo "<li>Add your business contact information</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<a href='?step=4' class='btn'>← Previous</a>";
    echo "<a href='?step=6' class='btn'>Complete Setup →</a>";
}

function setupStep6() {
    echo "<h2>🎉 Step 6: Setup Complete!</h2>";
    
    echo "<div class='step success'>";
    echo "<h3>✅ Installation Successful</h3>";
    echo "<p>Your outboard motors e-commerce website is now ready!</p>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>🔗 Quick Links</h3>";
    echo "<a href='index.php' class='btn btn-success'>View Website</a>";
    echo "<a href='admin/' class='btn'>Admin Panel</a>";
    echo "<a href='login.php' class='btn'>Customer Login</a>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>📚 Documentation</h3>";
    echo "<ul>";
    echo "<li><a href='README.md' target='_blank'>General Documentation</a></li>";
    echo "<li><a href='PAYPAL_INTEGRATION.md' target='_blank'>PayPal Integration Guide</a></li>";
    echo "<li><a href='INSTALLATION.md' target='_blank'>Installation Instructions</a></li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>🔧 Next Steps</h3>";
    echo "<ol>";
    echo "<li>Add your real products and images</li>";
    echo "<li>Set up your PayPal production credentials</li>";
    echo "<li>Configure email settings for notifications</li>";
    echo "<li>Test the complete purchase flow</li>";
    echo "<li>Set up SSL certificate for production</li>";
    echo "<li>Configure backup and monitoring</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>💡 Tips for Success</h3>";
    echo "<ul>";
    echo "<li>Test thoroughly in sandbox mode before going live</li>";
    echo "<li>Keep regular backups of your database</li>";
    echo "<li>Monitor error logs for issues</li>";
    echo "<li>Update PayPal webhooks for production</li>";
    echo "<li>Consider adding more payment methods</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<a href='?step=1' class='btn'>↻ Start Over</a>";
    echo "<a href='index.php' class='btn btn-success'>Launch Website 🚀</a>";
}

function setupComplete() {
    echo "<h2>Setup Complete</h2>";
    echo "<p>Your website is ready to use!</p>";
    echo "<a href='index.php' class='btn btn-success'>Go to Website</a>";
}
?>

<script>
// Auto-refresh functionality for development
if (window.location.search.includes('auto')) {
    setTimeout(() => {
        window.location.reload();
    }, 5000);
}
</script>
