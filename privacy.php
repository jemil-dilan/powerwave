<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = 'Privacy Policy';
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

            <h2>Information We Collect</h2>
            <p>We collect information you provide directly to us, such as when you create an account, make a purchase, or contact us for support.</p>
            
            <h3>Personal Information</h3>
            <ul>
                <li>Name and contact information</li>
                <li>Billing and shipping addresses</li>
                <li>Payment information (processed securely through PayPal)</li>
                <li>Account credentials</li>
            </ul>

            <h2>How We Use Your Information</h2>
            <p>We use the information we collect to:</p>
            <ul>
                <li>Process and fulfill your orders</li>
                <li>Communicate with you about your purchases</li>
                <li>Provide customer support</li>
                <li>Improve our products and services</li>
                <li>Comply with legal obligations</li>
            </ul>

            <h2>Information Sharing</h2>
            <p>We do not sell, trade, or rent your personal information to third parties. We may share your information with:</p>
            <ul>
                <li>Payment processors (PayPal) to process transactions</li>
                <li>Shipping companies to deliver your orders</li>
                <li>Service providers who assist with our operations</li>
                <li>Law enforcement when required by law</li>
            </ul>

            <h2>Data Security</h2>
            <p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>

            <h2>PayPal Integration</h2>
            <p>When you choose to pay with PayPal, your payment information is processed directly by PayPal according to their privacy policy. We do not store your PayPal login credentials or payment card information.</p>

            <h2>Cookies</h2>
            <p>We use cookies and similar technologies to enhance your browsing experience, remember your preferences, and analyze site usage.</p>

            <h2>Your Rights</h2>
            <p>You have the right to:</p>
            <ul>
                <li>Access your personal information</li>
                <li>Correct inaccurate information</li>
                <li>Delete your account and data</li>
                <li>Opt out of marketing communications</li>
            </ul>

            <h2>Contact Us</h2>
            <p>If you have any questions about this Privacy Policy, please contact us at:</p>
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
