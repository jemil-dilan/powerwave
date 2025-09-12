<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check admin access
if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle product actions
if ($_POST) {
    // Handle bulk actions
    if (isset($_POST['bulk_action']) && isset($_POST['selected_products'])) {
        $action = $_POST['bulk_action'];
        $selectedProducts = $_POST['selected_products'];
        $count = 0;
        
        foreach ($selectedProducts as $productId) {
            $productId = (int)$productId;
            if ($productId <= 0) continue;
            
            try {
                switch ($action) {
                    case 'delete':
                        // Check if product has related order items
                        $orderItems = $db->fetchColumn(
                            "SELECT COUNT(*) FROM order_items WHERE product_id = ?",
                            [$productId]
                        );
                        
                        if ($orderItems > 0) {
                            // Mark as discontinued instead
                            $db->update('products', [
                                'status' => 'discontinued',
                                'updated_at' => date('Y-m-d H:i:s')
                            ], 'id = ?', [$productId]);
                        } else {
                            // Safe to delete
                            $product = $db->fetchOne("SELECT main_image FROM products WHERE id = ?", [$productId]);
                            if ($product && $product['main_image']) {
                                $imagePath = "../uploads/products/" . $product['main_image'];
                                if (file_exists($imagePath)) {
                                    unlink($imagePath);
                                }
                            }
                            $db->delete('cart', 'product_id = ?', [$productId]);
                            $db->delete('wishlist', 'product_id = ?', [$productId]);
                            $db->delete('product_images', 'product_id = ?', [$productId]);
                            $db->delete('products', 'id = ?', [$productId]);
                        }
                        break;
                    case 'activate':
                        $db->update('products', ['status' => 'active'], 'id = ?', [$productId]);
                        break;
                    case 'deactivate':
                        $db->update('products', ['status' => 'inactive'], 'id = ?', [$productId]);
                        break;
                    case 'feature':
                        $db->update('products', ['featured' => 1], 'id = ?', [$productId]);
                        break;
                    case 'unfeature':
                        $db->update('products', ['featured' => 0], 'id = ?', [$productId]);
                        break;
                }
                $count++;
            } catch (Exception $e) {
                // Continue with other products
            }
        }
        
        if ($count > 0) {
            $actionText = ucfirst(str_replace('_', ' ', $action));
            showMessage("{$actionText} applied to {$count} product(s) successfully!", 'success');
        } else {
            showMessage('No products were processed.', 'error');
        }
    }
    
    if (isset($_POST['delete_product'])) {
        $productId = (int)$_POST['product_id'];
        try {
            // Check if product has related order items
            $orderItems = $db->fetchColumn(
                "SELECT COUNT(*) FROM order_items WHERE product_id = ?",
                [$productId]
            );
            
            if ($orderItems > 0) {
                // Don't delete, just mark as discontinued
                $db->update('products', [
                    'status' => 'discontinued',
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$productId]);
                showMessage('Product marked as discontinued (cannot delete as it has order history).', 'info');
            } else {
                // Safe to delete - no order history
                // First delete the product image if it exists
                $product = $db->fetchOne("SELECT main_image FROM products WHERE id = ?", [$productId]);
                if ($product && $product['main_image']) {
                    $imagePath = "../uploads/products/" . $product['main_image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                
                // Delete from cart if any
                $db->delete('cart', 'product_id = ?', [$productId]);
                
                // Delete from wishlist if any
                $db->delete('wishlist', 'product_id = ?', [$productId]);
                
                // Delete product images if any
                $db->delete('product_images', 'product_id = ?', [$productId]);
                
                // Finally delete the product
                $db->delete('products', 'id = ?', [$productId]);
                showMessage('Product deleted successfully!', 'success');
            }
        } catch (Exception $e) {
            showMessage('Error processing product deletion: ' . $e->getMessage(), 'error');
        }
    }
    
    if (isset($_POST['toggle_status'])) {
        $productId = (int)$_POST['product_id'];
        $newStatus = $_POST['new_status'];
        try {
            $db->update('products', ['status' => $newStatus], 'id = ?', [$productId]);
            showMessage('Product status updated!', 'success');
        } catch (Exception $e) {
            showMessage('Error updating status: ' . $e->getMessage(), 'error');
        }
    }
    
    if (isset($_POST['toggle_featured'])) {
        $productId = (int)$_POST['product_id'];
        $featured = (int)$_POST['featured'];
        try {
            $db->update('products', ['featured' => $featured], 'id = ?', [$productId]);
            showMessage('Featured status updated!', 'success');
        } catch (Exception $e) {
            showMessage('Error updating featured status: ' . $e->getMessage(), 'error');
        }
    }
}

// Get products with pagination
$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$limit = 20;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : null;
$status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

$whereClause = "1=1";
$params = [];

if ($search) {
    $whereClause .= " AND (p.name LIKE ? OR p.model LIKE ? OR p.sku LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($category) {
    $whereClause .= " AND p.category_id = ?";
    $params[] = $category;
}

if ($status) {
    $whereClause .= " AND p.status = ?";
    $params[] = $status;
}

$products = $db->fetchAll(
    "SELECT p.*, b.name as brand_name, c.name as category_name 
     FROM products p 
     LEFT JOIN brands b ON p.brand_id = b.id 
     LEFT JOIN categories c ON p.category_id = c.id 
     WHERE {$whereClause}
     ORDER BY p.created_at DESC 
     LIMIT ? OFFSET ?",
    array_merge($params, [$limit, $offset])
);

$totalProducts = $db->fetchColumn(
    "SELECT COUNT(*) FROM products p WHERE {$whereClause}",
    $params
);

$totalPages = ceil($totalProducts / $limit);

// Get categories for filter
$categories = getAllCategories();

$pageTitle = 'Manage Products';
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
        .product-image { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .status-active { background: #dcfce7; color: #059669; }
        .status-inactive { background: #fee2e2; color: #dc2626; }
        .status-discontinued { background: #f3f4f6; color: #6b7280; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .filters { background: white; padding: 16px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
    </style>
</head>
<body>
    <div class="admin-sidebar">
        <h2><i class="fas fa-anchor"></i> Admin</h2>
        <ul class="admin-nav">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="products.php" class="active"><i class="fas fa-cog"></i> Products</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="../index.php"><i class="fas fa-external-link-alt"></i> View Site</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>Manage Products</h1>
            <a href="add_product.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
        
        <?php displayMessage(); ?>
        
        <!-- Filters -->
        <div class="filters">
            <form method="GET" style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo $search; ?>" 
                       style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                
                <select name="category" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo sanitizeInput($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="status" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">All Statuses</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="discontinued" <?php echo $status === 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                </select>
                
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                <a href="products.php" class="btn btn-outline btn-sm">Reset</a>
            </form>
        </div>
        
        <?php if (empty($products)): ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 8px;">
                <i class="fas fa-box-open" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;"></i>
                <h3>No products found</h3>
                <p>Start by adding your first product.</p>
                <a href="add_product.php" class="btn btn-primary">Add Product</a>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <img src="<?php echo getProductImageUrl($product['main_image']); ?>" 
                                     alt="<?php echo sanitizeInput($product['name']); ?>" 
                                     class="product-image">
                            </td>
                            <td>
                                <strong><?php echo sanitizeInput($product['name']); ?></strong><br>
                                <small style="color: #6b7280;">
                                    <?php echo sanitizeInput($product['brand_name']); ?> • <?php echo sanitizeInput($product['model']); ?> • <?php echo $product['horsepower']; ?>HP
                                </small><br>
                                <small style="color: #9ca3af;">SKU: <?php echo sanitizeInput($product['sku']); ?></small>
                            </td>
                            <td><?php echo sanitizeInput($product['category_name']); ?></td>
                            <td>
                                <?php if ($product['sale_price']): ?>
                                    <span style="text-decoration: line-through; color: #9ca3af;"><?php echo formatPriceSafe($product['price']); ?></span><br>
                                    <strong style="color: #dc2626;"><?php echo formatPriceSafe($product['sale_price']); ?></strong>
                                <?php else: ?>
                                    <strong><?php echo formatPriceSafe($product['price']); ?></strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="color: <?php echo $product['stock_quantity'] <= $product['min_stock_level'] ? '#dc2626' : '#059669'; ?>;">
                                    <?php echo $product['stock_quantity']; ?>
                                </span>
                                <?php if ($product['stock_quantity'] <= $product['min_stock_level']): ?>
                                    <br><small style="color: #dc2626;">Low Stock</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <select name="new_status" onchange="this.form.submit()" 
                                            class="status-badge status-<?php echo $product['status']; ?>" 
                                            style="border: none; background: transparent; font-weight: 600;">
                                        <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="discontinued" <?php echo $product['status'] === 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                                    </select>
                                    <input type="hidden" name="toggle_status" value="1">
                                </form>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="featured" value="<?php echo $product['featured'] ? '0' : '1'; ?>">
                                    <button type="submit" name="toggle_featured" 
                                            style="background: none; border: none; font-size: 16px; cursor: pointer; color: <?php echo $product['featured'] ? '#f59e0b' : '#d1d5db'; ?>;">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="delete_product" class="btn btn-sm" style="background: #dc2626; color: white; border-color: #dc2626;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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
                        $url = 'products.php?' . http_build_query($params);
                        ?>
                        <a href="<?php echo $url; ?>" 
                           class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?> btn-sm">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding: 20px; background: #f9fafb; border-radius: 8px;">
            <h3>Quick Stats</h3>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 16px;">
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #059669;">
                        <?php echo $db->fetchColumn("SELECT COUNT(*) FROM products WHERE status = 'active'"); ?>
                    </div>
                    <div style="color: #6b7280;">Active Products</div>
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #dc2626;">
                        <?php echo $db->fetchColumn("SELECT COUNT(*) FROM products WHERE stock_quantity <= min_stock_level AND status = 'active'"); ?>
                    </div>
                    <div style="color: #6b7280;">Low Stock</div>
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #f59e0b;">
                        <?php echo $db->fetchColumn("SELECT COUNT(*) FROM products WHERE featured = 1 AND status = 'active'"); ?>
                    </div>
                    <div style="color: #6b7280;">Featured</div>
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #6b7280;">
                        <?php echo $db->fetchColumn("SELECT COUNT(*) FROM products WHERE status = 'inactive'"); ?>
                    </div>
                    <div style="color: #6b7280;">Inactive</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
