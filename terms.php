<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = 'Terms of Service';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/production-fixes.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="main-header">
                <div class="logo">
                    <a href="index.php">
                        <h1><i class="fas fa-anchor"></i> <?php echo SITE_NAME; ?></h1>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <div style="max-width: 800px; margin: 0 auto; background: white; padding: 32px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <h1><?php echo $pageTitle; ?></h1>
            <p><em>Last updated: <?php echo date('F j, Y'); ?></em></p>

            <h2>Acceptance of Terms</h2>
            <p>By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement.</p>

            <h2>Products and Services</h2>
            <p>We sell outboard motors and related marine equipment. All products are subject to availability and we reserve the right to discontinue any product at any time.</p>

            <h3>Product Information</h3>
            <ul>
                <li>We strive to provide accurate product descriptions and specifications</li>
                <li>Colors and appearance may vary from images shown</li>
                <li>Prices are subject to change without notice</li>
                <li>All prices are in USD unless otherwise specified</li>
            </ul>

            <h2>Ordering and Payment</h2>
            <p>When you place an order, you agree to provide accurate and complete information. We accept payment through:</p>
            <ul>
                <li>PayPal (processed securely through PayPal's platform)</li>
                <li>Bank transfer</li>
                <li>Other payment methods as available</li>
            </ul>

            <h3>Order Processing</h3>
            <ul>
                <li>Orders are processed within 1-2 business days</li>
                <li>You will receive email confirmation upon order placement</li>
                <li>Shipping notifications will be sent when your order ships</li>
                <li>We reserve the right to cancel orders due to pricing errors or product availability</li>
            </ul>

            <h2>Shipping and Delivery</h2>
            <p>Shipping costs and delivery times vary based on your location and the products ordered. Large outboard motors may require special shipping arrangements.</p>

            <h2>Returns and Warranties</h2>
            <p>All outboard motors come with manufacturer warranties. Returns are accepted within 30 days of purchase in original condition.</p>

            <h3>Return Policy</h3>
            <ul>
                <li>Items must be in original, unused condition</li>
                <li>Original packaging and documentation required</li>
                <li>Customer responsible for return shipping costs</li>
                <li>Refunds processed within 5-10 business days</li>
            </ul>

            <h2>PayPal Terms</h2>
            <p>When using PayPal for payment:</p>
            <ul>
                <li>PayPal's terms of service apply to the payment transaction</li>
                <li>Disputes should be filed through PayPal's resolution center</li>
                <li>Refunds will be processed back to your PayPal account</li>
                <li>PayPal buyer protection may apply to eligible purchases</li>
            </ul>

            <h2>Limitation of Liability</h2>
            <p>We shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of our products or services.</p>

            <h2>Privacy</h2>
            <p>Your privacy is important to us. Please review our <a href="privacy.php">Privacy Policy</a> to understand how we collect and use your information.</p>

            <h2>Changes to Terms</h2>
            <p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting on this website.</p>

            <h2>Contact Information</h2>
            <p>If you have any questions about these Terms of Service, please contact us:</p>
            <p>
                <strong><?php echo SITE_NAME; ?></strong><br>
                Email: <?php echo SITE_EMAIL; ?><br>
                Phone: (555) 123-4567
            </p>

            <div style="text-align: center; margin-top: 32px;">
                <a href="index.php" class="btn btn-primary">Return Home</a>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
