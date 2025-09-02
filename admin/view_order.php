<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdmin();

$db = Database::getInstance();

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$orderId) {
    redirect('orders.php');
}

// Handle order status update
if ($_POST && isset($_POST['update_status'])) {
    $newStatus = sanitizeInput($_POST['order_status']);
    $paymentStatus = sanitizeInput($_POST['payment_status']);
    $trackingNumber = sanitizeInput($_POST['tracking_number'] ?? '');
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    try {
        $updateData = [
            'status' => $newStatus,
            'payment_status' => $paymentStatus,
            'tracking_number' => $trackingNumber ? $trackingNumber : null,
            'notes' => $notes ? $notes : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $db->update('orders', $updateData, 'id = ?', [$orderId]);
        showMessage('Order updated successfully!', 'success');
    } catch (Exception $e) {
        showMessage('Error updating order: ' . $e->getMessage(), 'error');
    }
}

// Get order details with customer info
$order = $db->fetchOne(
    "SELECT o.*, u.first_name, u.last_name, u.email, u.phone
     FROM orders o 
     JOIN users u ON o.user_id = u.id
     WHERE o.id = ?",
    [$orderId]
);

if (!$order) {
    showMessage('Order not found', 'error');
    redirect('orders.php');
}

// Get order items
$orderItems = $db->fetchAll(
    "SELECT oi.*, p.name, p.model, p.sku, p.main_image, b.name as brand_name
     FROM order_items oi
     JOIN products p ON oi.product_id = p.id
     JOIN brands b ON p.brand_id = b.id
     WHERE oi.order_id = ?
     ORDER BY oi.id",
    [$orderId]
);

// Calculate subtotal from order items
$subtotal = 0;
foreach ($orderItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$pageTitle = 'Order #' . $order['order_number'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-sidebar { width: 250px; background: #1f2937; min-height: 100vh; position: fixed; left: 0; top: 0; }
        .admin-sidebar h2 { color: white; padding: 20px; margin: 0; border-bottom: 1px solid #374151; }
        .admin-nav { list-style: none; padding: 0; margin: 0; }
        .admin-nav li a { display: block; color: #d1d5db; padding: 12px 20px; text-decoration: none; }
        .admin-nav li a:hover, .admin-nav li a.active { background: #374151; color: white; }
        .admin-content { margin-left: 250px; padding: 20px; }
        .order-section { background: white; padding: 24px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e5e7eb; }
        .order-section h3 { margin-top: 0; margin-bottom: 16px; color: #1f2937; }
        .order-status { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-processing { background: #dbeafe; color: #1d4ed8; }
        .status-shipped { background: #d1fae5; color: #065f46; }
        .status-delivered { background: #ecfdf5; color: #047857; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        .payment-paid { background: #d1fae5; color: #065f46; }
        .payment-pending { background: #fef3c7; color: #d97706; }
        .payment-failed { background: #fee2e2; color: #dc2626; }
        .order-items { margin-top: 20px; }
        .item-row { display: flex; align-items: center; padding: 12px; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 12px; }
        .item-image { width: 60px; height: 60px; margin-right: 12px; }
        .item-image img { width: 100%; height: 100%; object-fit: cover; border-radius: 4px; }
        .item-details { flex: 1; }
        .item-name { font-weight: 600; margin-bottom: 4px; }
        .item-info { color: #6b7280; font-size: 14px; }
        .item-price { text-align: right; font-weight: 600; }
        .alert { padding: 12px 16px; margin-bottom: 16px; border-radius: 6px; border: 1px solid transparent; }
        .alert-success { background-color: #d1fae5; border-color: #a7f3d0; color: #065f46; }
        .alert-error { background-color: #fee2e2; border-color: #fca5a5; color: #dc2626; }
        .alert-info { background-color: #dbeafe; border-color: #93c5fd; color: #1e40af; }
    </style>
</head>
<body>
    <div class="admin-sidebar">
        <h2><i class="fas fa-anchor"></i> Admin</h2>
        <ul class="admin-nav">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-cog"></i> Products</a></li>
            <li><a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="../index.php"><i class="fas fa-external-link-alt"></i> View Site</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1><?php echo $pageTitle; ?></h1>
            <a href="orders.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
        
        <?php displayMessage(); ?>
        
        <!-- Order Summary -->
        <div class="order-section">
            <h3>Order Summary</h3>
            <div class="grid grid-4">
                <div>
                    <strong>Order Date:</strong><br>
                    <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?>
                </div>
                <div>
                    <strong>Order Status:</strong><br>
                    <span class="order-status status-<?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>
                <div>
                    <strong>Payment Status:</strong><br>
                    <span class="order-status payment-<?php echo $order['payment_status']; ?>">
                        <?php echo ucfirst($order['payment_status']); ?>
                    </span>
                </div>
                <div>
                    <strong>Total Amount:</strong><br>
                    <span style="font-size: 18px; font-weight: bold; color: #059669;">
                        <?php echo formatPrice($order['total_amount']); ?>
                    </span>
                </div>
            </div>
            
            <?php if ($order['tracking_number']): ?>
                <div style="margin-top: 16px;">
                    <strong>Tracking Number:</strong> <?php echo sanitizeInput($order['tracking_number']); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($order['notes']): ?>
                <div style="margin-top: 16px;">
                    <strong>Notes:</strong><br>
                    <div style="background: #f9fafb; padding: 12px; border-radius: 6px; margin-top: 4px;">
                        <?php echo nl2br(sanitizeInput($order['notes'])); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Customer Information -->
        <div class="order-section">
            <h3>Customer Information</h3>
            <div class="grid grid-2">
                <div>
                    <strong>Name:</strong><br>
                    <?php echo sanitizeInput($order['first_name'] . ' ' . $order['last_name']); ?>
                </div>
                <div>
                    <strong>Email:</strong><br>
                    <a href="mailto:<?php echo sanitizeInput($order['email']); ?>"><?php echo sanitizeInput($order['email']); ?></a>
                </div>
                <div>
                    <strong>Phone:</strong><br>
                    <?php echo $order['phone'] ? sanitizeInput($order['phone']) : 'Not provided'; ?>
                </div>
                <div>
                    <strong>Payment Method:</strong><br>
                    <?php echo ucfirst(sanitizeInput($order['payment_method'])); ?>
                </div>
            </div>
        </div>
        
        <!-- Shipping Address -->
        <?php if ($order['shipping_address']): ?>
        <div class="order-section">
            <h3>Shipping Address</h3>
            <div style="line-height: 1.6; background: #f9fafb; padding: 12px; border-radius: 6px;">
                <?php echo nl2br(sanitizeInput($order['shipping_address'])); ?>
            </div>
            
            <?php if ($order['shipping_cost'] > 0): ?>
                <div style="margin-top: 12px;">
                    <strong>Shipping Cost:</strong> <?php echo formatPrice($order['shipping_cost']); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Order Items -->
        <div class="order-section">
            <h3>Order Items</h3>
            <div class="order-items">
                <?php foreach ($orderItems as $item): ?>
                    <div class="item-row">
                        <div class="item-image">
                            <img src="<?php echo getProductImageUrl($item['main_image']); ?>" 
                                 alt="<?php echo sanitizeInput($item['name']); ?>">
                        </div>
                        <div class="item-details">
                            <div class="item-name"><?php echo sanitizeInput($item['name']); ?></div>
                            <div class="item-info">
                                <?php echo sanitizeInput($item['brand_name']); ?> • 
                                Model: <?php echo sanitizeInput($item['model']); ?> • 
                                SKU: <?php echo sanitizeInput($item['sku']); ?><br>
                                Quantity: <?php echo $item['quantity']; ?> × 
                                <?php echo formatPrice($item['price']); ?>
                            </div>
                        </div>
                        <div class="item-price">
                            <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Order Totals -->
                <div style="border-top: 2px solid #e5e7eb; padding-top: 16px; margin-top: 16px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span>Subtotal:</span>
                        <span><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    <?php if ($order['tax_amount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span>Tax:</span>
                        <span><?php echo formatPrice($order['tax_amount']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['shipping_cost'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span>Shipping:</span>
                        <span><?php echo formatPrice($order['shipping_cost']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: bold; border-top: 1px solid #e5e7eb; padding-top: 8px;">
                        <span>Total:</span>
                        <span><?php echo formatPrice($order['total_amount']); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Update Order Status -->
        <div class="order-section">
            <h3>Update Order Status</h3>
            <form method="POST">
                <div class="grid grid-2">
                    <div class="form-group">
                        <label>Order Status</label>
                        <select name="order_status" class="input" required>
                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Payment Status</label>
                        <select name="payment_status" class="input" required>
                            <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            <option value="refunded" <?php echo $order['payment_status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Tracking Number</label>
                    <input type="text" name="tracking_number" class="input" 
                           value="<?php echo sanitizeInput($order['tracking_number'] ?? ''); ?>"
                           placeholder="Enter tracking number (optional)">
                </div>
                
                <div class="form-group">
                    <label>Internal Notes</label>
                    <textarea name="notes" class="input" rows="4" 
                              placeholder="Add internal notes about this order..."><?php echo sanitizeInput($order['notes'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" name="update_status" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Order
                </button>
            </form>
        </div>
    </div>
</body>
</html>
