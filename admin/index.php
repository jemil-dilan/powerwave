<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdmin();

$db = Database::getInstance();
$stats = [
    'total_products' => $db->fetchColumn("SELECT COUNT(*) FROM products"),
    'total_orders' => $db->fetchColumn("SELECT COUNT(*) FROM orders"),
    'total_users' => $db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'customer'"),
    'total_revenue' => $db->fetchColumn("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid'"),
    'pending_orders' => $db->fetchColumn("SELECT COUNT(*) FROM orders WHERE status = 'pending'"),
    'low_stock' => $db->fetchColumn("SELECT COUNT(*) FROM products WHERE stock_quantity <= min_stock_level AND status = 'active'")
];

$recentOrders = $db->fetchAll(
    "SELECT o.*, u.first_name, u.last_name 
     FROM orders o 
     JOIN users u ON o.user_id = u.id 
     ORDER BY o.created_at DESC 
     LIMIT 8"
);

$recentUsers = $db->fetchAll(
    "SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC LIMIT 5"
);

$topProducts = $db->fetchAll(
    "SELECT p.name, p.price, SUM(oi.quantity) as sold_quantity
     FROM products p 
     JOIN order_items oi ON p.id = oi.product_id
     JOIN orders o ON oi.order_id = o.id
     WHERE o.status != 'cancelled'
     GROUP BY p.id
     ORDER BY sold_quantity DESC
     LIMIT 5"
);

$pageTitle = 'Admin Dashboard';
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
        .stat-card { background: white; border-radius: 8px; padding: 20px; border: 1px solid #e5e7eb; text-align: center; }
        .stat-value { font-size: 32px; font-weight: bold; color: #0ea5e9; margin-bottom: 8px; }
        .stat-label { color: #6b7280; }
        .admin-table { width: 100%; background: white; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden; }
        .admin-table th, .admin-table td { padding: 12px; text-align: left; border-bottom: 1px solid #f3f4f6; }
        .admin-table th { background: #f9fafb; font-weight: 600; }
    </style>
</head>
<body>
    <div class="admin-sidebar">
        <h2><i class="fas fa-anchor"></i> Admin</h2>
        <ul class="admin-nav">
            <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-cog"></i> Products</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="../index.php"><i class="fas fa-external-link-alt"></i> View Site</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <h1>Dashboard</h1>
        
        <div class="grid grid-4" style="margin: 20px 0;">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats['total_products']); ?></div>
                <div class="stat-label">Total Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats['total_orders']); ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                <div class="stat-label">Customers</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo formatPrice($stats['total_revenue']); ?></div>
                <div class="stat-label">Revenue</div>
            </div>
        </div>
        
        <h2>Recent Orders</h2>
        <?php if (!$recentOrders): ?>
            <p>No orders yet.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><?php echo sanitizeInput($order['order_number']); ?></td>
                            <td><?php echo sanitizeInput($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                            <td><?php echo ucfirst($order['status']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
