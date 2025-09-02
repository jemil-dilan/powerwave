<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdmin();

$db = Database::getInstance();

// Handle user actions
if ($_POST) {
    if (isset($_POST['toggle_role'])) {
        $userId = (int)$_POST['user_id'];
        $newRole = sanitizeInput($_POST['new_role']);
        
        try {
            $db->update('users', ['role' => $newRole], 'id = ?', [$userId]);
            showMessage('User role updated successfully!', 'success');
        } catch (Exception $e) {
            showMessage('Error updating user role: ' . $e->getMessage(), 'error');
        }
    }
    
    if (isset($_POST['delete_user'])) {
        $userId = (int)$_POST['user_id'];
        
        // Check if user has orders
        $orderCount = $db->fetchColumn("SELECT COUNT(*) FROM orders WHERE user_id = ?", [$userId]);
        
        if ($orderCount > 0) {
            showMessage('Cannot delete user with existing orders. Consider deactivating instead.', 'error');
        } else {
            try {
                $db->delete('users', 'id = ?', [$userId]);
                showMessage('User deleted successfully!', 'success');
            } catch (Exception $e) {
                showMessage('Error deleting user: ' . $e->getMessage(), 'error');
            }
        }
    }
}

// Get users with pagination and filtering
$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$limit = 20;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$role = isset($_GET['role']) ? sanitizeInput($_GET['role']) : '';

$whereClause = "1=1";
$params = [];

if ($search) {
    $whereClause .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR username LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($role) {
    $whereClause .= " AND role = ?";
    $params[] = $role;
}

$users = $db->fetchAll(
    "SELECT u.*, 
     (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
     (SELECT SUM(total_amount) FROM orders WHERE user_id = u.id AND payment_status = 'paid') as total_spent
     FROM users u 
     WHERE {$whereClause}
     ORDER BY u.created_at DESC 
     LIMIT ? OFFSET ?",
    array_merge($params, [$limit, $offset])
);

$totalUsers = $db->fetchColumn(
    "SELECT COUNT(*) FROM users WHERE {$whereClause}",
    $params
);

$totalPages = ceil($totalUsers / $limit);

$pageTitle = 'Manage Users';
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
        .role-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .role-admin { background: #fef3c7; color: #d97706; }
        .role-customer { background: #dbeafe; color: #1d4ed8; }
        .filters { background: white; padding: 16px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
        .user-stats { background: #f9fafb; padding: 8px; border-radius: 6px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="admin-sidebar">
        <h2><i class="fas fa-anchor"></i> Admin</h2>
        <ul class="admin-nav">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-cog"></i> Products</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="../index.php"><i class="fas fa-external-link-alt"></i> View Site</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <h1>Manage Users</h1>
        
        <?php displayMessage(); ?>
        
        <!-- Filters -->
        <div class="filters">
            <form method="GET" style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                <input type="text" name="search" placeholder="Search users..." value="<?php echo $search; ?>" 
                       style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                
                <select name="role" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">All Roles</option>
                    <option value="customer" <?php echo $role === 'customer' ? 'selected' : ''; ?>>Customers</option>
                    <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Administrators</option>
                </select>
                
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                <a href="users.php" class="btn btn-outline btn-sm">Reset</a>
            </form>
        </div>
        
        <?php if (empty($users)): ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 8px;">
                <i class="fas fa-users" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;"></i>
                <h3>No users found</h3>
                <p>Users will appear here when they register.</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <strong><?php echo sanitizeInput($user['first_name'] . ' ' . $user['last_name']); ?></strong><br>
                                <small style="color: #6b7280;">@<?php echo sanitizeInput($user['username']); ?></small>
                            </td>
                            <td>
                                <strong><?php echo sanitizeInput($user['email']); ?></strong><br>
                                <?php if ($user['phone']): ?>
                                    <small style="color: #6b7280;"><?php echo sanitizeInput($user['phone']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <select name="new_role" onchange="this.form.submit()" 
                                                class="role-badge role-<?php echo $user['role']; ?>" 
                                                style="border: none; background: transparent;">
                                            <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                        <input type="hidden" name="toggle_role" value="1">
                                    </form>
                                <?php else: ?>
                                    <span class="role-badge role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?> (You)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['order_count'] > 0): ?>
                                    <strong><?php echo $user['order_count']; ?></strong> orders
                                <?php else: ?>
                                    <span style="color: #9ca3af;">No orders</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['total_spent']): ?>
                                    <strong><?php echo formatPrice($user['total_spent']); ?></strong>
                                <?php else: ?>
                                    <span style="color: #9ca3af;">$0.00</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                <div class="user-stats">
                                    Last: <?php echo date('M j', strtotime($user['updated_at'])); ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="btn btn-sm" style="background: #dc2626; color: white; border-color: #dc2626;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #9ca3af; font-size: 12px;">Current User</span>
                                <?php endif; ?>
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
                        $url = 'users.php?' . http_build_query($params);
                        ?>
                        <a href="<?php echo $url; ?>" 
                           class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?> btn-sm">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- User Statistics -->
        <div style="margin-top: 30px; padding: 20px; background: #f9fafb; border-radius: 8px;">
            <h3>User Statistics</h3>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 16px;">
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #3b82f6;">
                        <?php echo $db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'customer'"); ?>
                    </div>
                    <div style="color: #6b7280;">Total Customers</div>
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #f59e0b;">
                        <?php echo $db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'admin'"); ?>
                    </div>
                    <div style="color: #6b7280;">Administrators</div>
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #059669;">
                        <?php echo $db->fetchColumn("SELECT COUNT(DISTINCT user_id) FROM orders"); ?>
                    </div>
                    <div style="color: #6b7280;">Active Buyers</div>
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #8b5cf6;">
                        <?php echo $db->fetchColumn("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"); ?>
                    </div>
                    <div style="color: #6b7280;">New (30 days)</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
