<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdmin();

$db = Database::getInstance();

// Handle order status updates
if ($_POST) {
    if (isset($_POST['update_status'])) {
        $orderId = (int)$_POST['order_id'];
        $newStatus = sanitizeInput($_POST['new_status']);
        $paymentStatus = sanitizeInput($_POST['payment_status']);
        
        try {
            $db->update('orders', [
                'status' => $newStatus,
                'payment_status' => $paymentStatus
            ], 'id = ?', [$orderId]);
            
            showMessage('Order status updated successfully!', 'success');
        } catch (Exception $e) {
            showMessage('Error updating order: ' . $e->getMessage(), 'error');
        }
    }
}

// Get orders with pagination and filtering
$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$limit = 20;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$paymentStatus = isset($_GET['payment_status']) ? sanitizeInput($_GET['payment_status']) : '';

$whereClause = "1=1";
$params = [];

if ($search) {
    $whereClause .= " AND (o.order_number LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($status) {
    $whereClause .= " AND o.status = ?";
    $params[] = $status;
}

if ($paymentStatus) {
    $whereClause .= " AND o.payment_status = ?";
    $params[] = $paymentStatus;
}

$orders = $db->fetchAll(
    "SELECT o.*, u.first_name, u.last_name, u.email
     FROM orders o 
     JOIN users u ON o.user_id = u.id 
     WHERE {$whereClause}
     ORDER BY o.created_at DESC 
     LIMIT ? OFFSET ?",
    array_merge($params, [$limit, $offset])
);

$totalOrders = $db->fetchColumn(
    "SELECT COUNT(*) FROM orders o JOIN users u ON o.user_id = u.id WHERE {$whereClause}",
    $params
);

$totalPages = ceil($totalOrders / $limit);

$pageTitle = 'Manage Orders';
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
        .admin-table { width: 100%; background: white; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden; }
        .admin-table th, .admin-table td { padding: 12px; text-align: left; border-bottom: 1px solid #f3f4f6; }
        .admin-table th { background: #f9fafb; font-weight: 600; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-processing { background: #dbeafe; color: #1d4ed8; }
        .status-shipped { background: #e0e7ff; color: #6366f1; }
        .status-delivered { background: #dcfce7; color: #059669; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        .payment-pending { background: #fef3c7; color: #d97706; }
        .payment-paid { background: #dcfce7; color: #059669; }
        .payment-failed { background: #fee2e2; color: #dc2626; }
        .filters { background: white; padding: 16px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
        .order-details { background: #f9fafb; padding: 8px; border-radius: 6px; font-size: 12px; }
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
        <h1>Manage Orders</h1>
        
        <?php displayMessage(); ?>
        
        <!-- Filters -->
        <div class="filters">
            <form method="GET" style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                <input type="text" name="search" placeholder="Search orders..." value="<?php echo $search; ?>" 
                       style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                
                <select name="status" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">All Order Status</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                
                <select name="payment_status" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">All Payment Status</option>
                    <option value="pending" <?php echo $paymentStatus === 'pending' ? 'selected' : ''; ?>>Payment Pending</option>
                    <option value="paid" <?php echo $paymentStatus === 'paid' ? 'selected' : ''; ?>>Paid</option>
                    <option value="failed" <?php echo $paymentStatus === 'failed' ? 'selected' : ''; ?>>Failed</option>
                    <option value="refunded" <?php echo $paymentStatus === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                </select>
                
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                <a href="orders.php" class="btn btn-outline btn-sm">Reset</a>
            </form>
        </div>
        
        <?php if (empty($orders)): ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 8px;">
                <i class="fas fa-shopping-cart" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;"></i>
                <h3>No orders found</h3>
                <p>Orders will appear here when customers make purchases.</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment Method</th>
                        <th>Order Status</th>
                        <th>Payment Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <strong><?php echo sanitizeInput($order['order_number']); ?></strong>
                                <div class="order-details">
                                    Items: <?php echo $db->fetchColumn("SELECT SUM(quantity) FROM order_items WHERE order_id = ?", [$order['id']]); ?> •
                                    Shipping: <?php echo formatPrice($order['shipping_cost']); ?>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo sanitizeInput($order['first_name'] . ' ' . $order['last_name']); ?></strong><br>
                                <small style="color: #6b7280;"><?php echo sanitizeInput($order['email']); ?></small>
                            </td>
                            <td>
                                <strong><?php echo formatPrice($order['total_amount']); ?></strong>
                                <?php if ($order['tax_amount'] > 0): ?>
                                    <br><small style="color: #6b7280;">Tax: <?php echo formatPrice($order['tax_amount']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo ucfirst($order['payment_method']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="payment_status" value="<?php echo $order['payment_status']; ?>">
                                    <select name="new_status" onchange="this.form.submit()" 
                                            class="status-badge status-<?php echo $order['status']; ?>" 
                                            style="border: none; background: transparent;">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="new_status" value="<?php echo $order['status']; ?>">
                                    <select name="payment_status" onchange="this.form.submit()" 
                                            class="status-badge payment-<?php echo $order['payment_status']; ?>" 
                                            style="border: none; background: transparent;">
                                        <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                        <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                        <option value="refunded" <?php echo $order['payment_status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                            <td>
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div style="display: flex; justify-content: center; gap: 8px; margin-top: 20px;">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php
                        $params = $_GET;
                        $params['page'] = $i;
                        $url = 'orders.php?' . http_build_query($params);
                        ?>
                        <a href="<?php echo $url; ?>" 
                           class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?> btn-sm">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Order Statistics -->
        <div style="margin-top: 30px; padding: 20px; background: #f9fafb; border-radius: 8px;">
            <h3>Order Statistics</h3>
            <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-top: 16px;">
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #f59e0b;">
                        <?php echo $db->fetchColumn("SELECT COUNT(*) FROM orders WHERE status = 'pending'"); ?>
                    </div>
                    <div style="color: #6b7280;">Pending Orders</div>
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #3b82f6;">
                        <?php echo $db->fetchColumn("SELECT COUNT(*) FROM orders WHERE status = 'processing'"); ?>
                    </div>
                    <div style="color: #6b7280;">Processing</div>
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #8b5cf6;">
                        <?php echo $db->fetchColumn("SELECT COUNT(*) FROM orders WHERE status = 'shipped'"); ?>
                    </div>
                    <div style="color: #6b7280;">Shipped</div>
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #059669;">
                        <?php echo $db->fetchColumn("SELECT COUNT(*) FROM orders WHERE status = 'delivered'"); ?>
                    </div>
                    <div style="color: #6b7280;">Delivered</div>
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #dc2626;">
                        <?php echo $db->fetchColumn("SELECT COUNT(*) FROM orders WHERE payment_status = 'pending'"); ?>
                    </div>
                    <div style="color: #6b7280;">Payment Pending</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
