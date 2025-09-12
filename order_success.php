<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

$orderNumber = isset($_GET['order']) ? sanitizeInput($_GET['order']) : '';
$paymentMethod = isset($_GET['method']) ? sanitizeInput($_GET['method']) : '';

if (empty($orderNumber)) {
    redirect('index.php');
}

// Get order details
$db = Database::getInstance();
$order = $db->fetchOne(
    "SELECT * FROM orders WHERE order_number = ? AND user_id = ?",
    [$orderNumber, $_SESSION['user_id']]
);

if (!$order) {
    showMessage('Order not found', 'error');
    redirect('index.php');
}

// Get order items
$orderItems = $db->fetchAll(
    "SELECT oi.*, p.name, p.model, b.name as brand_name
     FROM order_items oi
     JOIN products p ON oi.product_id = p.id
     JOIN brands b ON p.brand_id = b.id
     WHERE oi.order_id = ?",
    [$order['id']]
);

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
    <style>
        .success-header { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 48px 0; text-align: center; }
        .success-icon { font-size: 64px; margin-bottom: 16px; }
        .order-details { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin: 24px 0; }
        .order-items-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .order-items-table th, .order-items-table td { padding: 12px; text-align: left; border-bottom: 1px solid #f3f4f6; }
        .order-items-table th { background: #f9fafb; font-weight: 600; }
        .payment-info { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; margin: 16px 0; }
        .next-steps { background: #eff6ff; border: 1px solid #93c5fd; border-radius: 8px; padding: 16px; margin: 16px 0; }
    </style>

</head>
<body>
    <div class="success-header">
        <div class="container">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Order Confirmed!</h1>
            <p>Thank you for your purchase. Your order has been received and is being processed.</p>
        </div>
    </div>

    <main class="container">
        <div class="order-details">
            <div class="grid grid-2">
                <div>
                    <h3>Order Information</h3>
                    <p><strong>Order Number:</strong> <?php echo sanitizeInput($order['order_number']); ?></p>
                    <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                    <p><strong>Total Amount:</strong> <?php echo formatPriceSafe($order['total_amount']); ?></p>
                    <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                    <p><strong>Status:</strong>
                        <span style="color: #f59e0b; font-weight: 600;"><?php echo ucfirst($order['status']); ?></span>
                    </p>
                </div>
                <div>
                    <h3>Payment Status</h3>
                    <?php if ($order['payment_status'] === 'paid'): ?>
                        <div class="payment-info">
                            <p style="margin: 0; color: #166534;"><i class="fas fa-check-circle"></i> Payment Confirmed</p>
                            <p style="margin: 8px 0 0; color: #166534; font-size: 14px;">Your payment has been successfully processed.</p>
                        </div>
                    <?php else: ?>
                        <div class="next-steps">
                            <p style="margin: 0; color: #1e40af;"><i class="fas fa-info-circle"></i> Payment Pending</p>
                            <?php if ($order['payment_method'] === 'bank'): ?>
                                <p style="margin: 8px 0 0; color: #1e40af; font-size: 14px;">Bank transfer instructions have been sent to your email.</p>
                            <?php else: ?>
                                <p style="margin: 8px 0 0; color: #1e40af; font-size: 14px;">We're processing your payment and will update you shortly.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="order-details">
            <h3>Order Items</h3>
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                <strong><?php echo sanitizeInput($item['name']); ?></strong><br>
                                <small style="color: #6b7280;"><?php echo sanitizeInput($item['brand_name']); ?> - <?php echo sanitizeInput($item['model']); ?></small>
                            </td>
                            <td><?php echo formatPriceSafe($item['price']); ?></td>
                            <td><?php echo (int)$item['quantity']; ?></td>
                            <td><?php echo formatPriceSafe($item['total']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #e2e8f0;">
                        <td colspan="2"><strong>Subtotal</strong></td>
                        <td colspan="2"><strong><?php echo formatPriceSafe($order['total_amount'] - $order['shipping_cost'] - $order['tax_amount']); ?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="2">Shipping</td>
                        <td colspan="2"><?php echo formatPriceSafe($order['shipping_cost']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2">Tax</td>
                        <td colspan="2"><?php echo formatPriceSafe($order['tax_amount']); ?></td>
                    </tr>
                    <tr style="font-size: 18px; font-weight: 700;">
                        <td colspan="2"><strong>Total</strong></td>
                        <td colspan="2"><strong><?php echo formatPriceSafe($order['total_amount']); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if ($order['payment_method'] === 'bank' && $order['payment_status'] === 'pending'): ?>
        <div class="order-details">
            <h3><i class="fas fa-university"></i> Bank Transfer Instructions</h3>
            <div class="next-steps">
                <p><strong>Please transfer <?php echo formatPriceSafe($order['total_amount']); ?> to:</strong></p>
                <p>
                    <strong>Account Name:</strong> <?php echo BANK_ACCOUNT_NAME; ?><br>
                    <strong>Account Number:</strong> <?php echo BANK_ACCOUNT_NUMBER; ?><br>
                    <strong>Routing Number:</strong> <?php echo BANK_ROUTING_NUMBER; ?><br>
                    <strong>Reference:</strong> <?php echo $order['order_number']; ?>
                </p>
                <p><strong>Important:</strong> Please include your order number (<?php echo $order['order_number']; ?>) in the transfer reference to ensure quick processing.</p>
            </div>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin: 32px 0;">
            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
            <a href="index.php" class="btn btn-outline" style="margin-left: 12px;">Back to Home</a>
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