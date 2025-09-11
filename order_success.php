<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

$orderNumber = sanitizeInput($_GET['order'] ?? '');
$paymentMethod = sanitizeInput($_GET['method'] ?? '');

if (!$orderNumber || !$paymentMethod) {
    redirect('index.php');
}

// Verify order belongs to current user
$db = Database::getInstance();
$order = $db->fetchOne(
    "SELECT * FROM orders WHERE order_number = ? AND user_id = ?",
    [$orderNumber, $_SESSION['user_id']]
);

if (!$order) {
    showMessage('Order not found.', 'error');
    redirect('index.php');
}

$pageTitle = 'Order Confirmation';
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
        <div style="max-width: 800px; margin: 0 auto;">
            <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 32px; text-align: center;">
                <i class="fas fa-check-circle" style="font-size: 64px; color: #10b981; margin-bottom: 16px;"></i>
                <h1 style="color: #10b981; margin-bottom: 8px;">Order Placed Successfully!</h1>
                <p style="font-size: 18px; color: #64748b; margin-bottom: 24px;">Thank you for your order. We've received it and will process it shortly.</p>
                
                <div style="background: #f8fafc; border-radius: 8px; padding: 20px; margin: 24px 0; text-align: left;">
                    <h3>Order Details</h3>
                    <p><strong>Order Number:</strong> <?php echo sanitizeInput($orderNumber); ?></p>
                    <p><strong>Total Amount:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
                    <p><strong>Payment Method:</strong> <?php echo ucfirst($paymentMethod); ?></p>
                    <p><strong>Order Date:</strong> <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></p>
                </div>

                <?php if ($paymentMethod === 'paypal'): ?>
                    <div style="background: #e0f2fe; border-radius: 8px; padding: 20px; margin: 24px 0; text-align: left;">
                        <h3><i class="fab fa-paypal"></i> PayPal Payment</h3>
                        <p>To complete your payment using PayPal:</p>
                        <ol>
                            <li>Click the PayPal button below</li>
                            <li>Log in to your PayPal account</li>
                            <li>Complete the payment</li>
                        </ol>
                        
                        <!-- PayPal Button Placeholder -->
                        <div style="border: 2px dashed #0ea5e9; border-radius: 8px; padding: 20px; text-align: center; margin: 16px 0;">
                            <p style="color: #0ea5e9; font-weight: bold;">PayPal Button Integration</p>
                            <p style="color: #64748b; font-size: 14px;">
                                Replace PAYPAL_CLIENT_ID in includes/config.php with your real PayPal client ID<br>
                                Current placeholder: <?php echo PAYPAL_CLIENT_ID; ?>
                            </p>
                            <div style="background: #0070ba; color: white; padding: 12px 24px; border-radius: 6px; display: inline-block; margin: 8px;">
                                <i class="fab fa-paypal"></i> Pay with PayPal
                            </div>
                        </div>
                    </div>

                <?php elseif ($paymentMethod === 'bank'): ?>
                    <div style="background: #fef3c7; border-radius: 8px; padding: 20px; margin: 24px 0; text-align: left;">
                        <h3><i class="fas fa-university"></i> Bank Transfer Instructions</h3>
                        <p>Please transfer the total amount to our bank account:</p>
                        <div style="background: white; border-radius: 6px; padding: 16px; margin: 16px 0;">
                            <p><strong>Account Name:</strong> <?php echo BANK_ACCOUNT_NAME; ?></p>
                            <p><strong>Account Number:</strong> <?php echo BANK_ACCOUNT_NUMBER; ?></p>
                            <p><strong>Routing Number:</strong> <?php echo BANK_ROUTING_NUMBER; ?></p>
                            <p><strong>Amount:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
                            <p><strong>Reference:</strong> <?php echo $orderNumber; ?></p>
                        </div>
                        <p style="color: #d97706;"><strong>Important:</strong> Please include the order number as reference in your transfer.</p>
                        <p style="font-size: 14px; color: #64748b;">Update bank details in includes/config.php with your real account information.</p>
                    </div>

                <?php elseif ($paymentMethod === 'applepay'): ?>
                    <div style="background: #f3f4f6; border-radius: 8px; padding: 20px; margin: 24px 0; text-align: left;">
                        <h3><i class="fab fa-apple"></i> Apple Pay</h3>
                        <p>Complete your payment using Apple Pay on your Apple device:</p>
                        
                        <div style="border: 2px dashed #6b7280; border-radius: 8px; padding: 20px; text-align: center; margin: 16px 0;">
                            <p style="color: #6b7280; font-weight: bold;">Apple Pay Integration</p>
                            <p style="color: #64748b; font-size: 14px;">
                                Replace APPLE_PAY_MERCHANT_ID in includes/config.php<br>
                                Current placeholder: <?php echo APPLE_PAY_MERCHANT_ID; ?>
                            </p>
                            <div style="background: #000; color: white; padding: 12px 24px; border-radius: 6px; display: inline-block; margin: 8px;">
                                <i class="fab fa-apple"></i> Pay
                            </div>
                        </div>
                    </div>

                <?php elseif ($paymentMethod === 'cashapp'): ?>
                    <div style="background: #dcfce7; border-radius: 8px; padding: 20px; margin: 24px 0; text-align: left;">
                        <h3><i class="fas fa-dollar-sign"></i> Cash App Payment</h3>
                        <p>Send payment via Cash App:</p>
                        <div style="background: white; border-radius: 6px; padding: 16px; margin: 16px 0;">
                            <p><strong>Cash App Tag:</strong> <?php echo CASH_APP_CASHTAG; ?></p>
                            <p><strong>Amount:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
                            <p><strong>Note:</strong> <?php echo $orderNumber; ?></p>
                        </div>
                        <p style="color: #059669;"><strong>Important:</strong> Please include the order number in the payment note.</p>
                        <p style="font-size: 14px; color: #64748b;">Update CASH_APP_CASHTAG in includes/config.php with your real Cash App tag.</p>
                    </div>
                <?php endif; ?>

                <div style="background: #f1f5f9; border-radius: 8px; padding: 20px; margin: 24px 0; text-align: left;">
                    <h3>What Happens Next?</h3>
                    <ol>
                        <li>Complete your payment using the method selected above</li>
                        <li>We will verify your payment within 24 hours</li>
                        <li>Once confirmed, we'll prepare your order for shipping</li>
                        <li>You'll receive a tracking number via email</li>
                        <li>Your outboard motor will be delivered to your address</li>
                    </ol>
                </div>

                <div style="text-align: center; margin: 32px 0;">
                    <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                    <a href="products.php" class="btn btn-outline" style="margin-left: 8px;">View Products</a>
                </div>

                <p style="color: #64748b; font-size: 14px;">
                    If you have any questions about your order, please contact us at <?php echo SITE_EMAIL; ?> or call (555) 123-4567.
                </p>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                <div class="footer-links">
                    <a href="privacy.php">Privacy Policy</a>
                    <a href="terms.php">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
