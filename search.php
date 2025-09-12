<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : null;
$brand = isset($_GET['brand']) ? (int)$_GET['brand'] : null;
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;

$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$limit = PRODUCTS_PER_PAGE;
$offset = ($page - 1) * $limit;

$products = [];
$totalResults = 0;

if ($query || $category || $brand || $minPrice || $maxPrice) {
    $products = searchProducts($query, $category, $brand, $minPrice, $maxPrice, $limit, $offset);
    
    // Get total count for pagination
    $db = Database::getInstance();
    $params = [];
    $whereConditions = ["p.status = 'active'"];
    
    if ($query) {
        $whereConditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.model LIKE ?)";
        $searchTerm = "%{$query}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($category) {
        $whereConditions[] = "p.category_id = ?";
        $params[] = $category;
    }
    
    if ($brand) {
        $whereConditions[] = "p.brand_id = ?";
        $params[] = $brand;
    }
    
    if ($minPrice !== null) {
        $whereConditions[] = "p.price >= ?";
        $params[] = $minPrice;
    }
    
    if ($maxPrice !== null) {
        $whereConditions[] = "p.price <= ?";
        $params[] = $maxPrice;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    $totalResults = $db->fetchColumn(
        "SELECT COUNT(*) FROM products p WHERE {$whereClause}",
        $params
    );
}

$categories = getAllCategories();
$brands = getAllBrands();
$totalPages = ceil($totalResults / $limit);

$pageTitle = $query ? "Search Results for '{$query}'" : 'Search Products';
$csrfToken = generateCSRFToken();
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
    <meta name="csrf-token" content="<?php echo $csrfToken; ?>">

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
                <div class="search-bar">
                    <form action="search.php" method="GET" class="search-form">
                        <input type="text" name="q" placeholder="Search outboard motors..." value="<?php echo sanitizeInput($query); ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="cart-info">
                    <a href="cart.php" class="cart-link">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo getCartItemCount(isLoggedIn() ? $_SESSION['user_id'] : null); ?></span>
                        <span class="cart-total"><?php echo getCartTotalForDisplay(isLoggedIn() ? $_SESSION['user_id'] : null); ?></span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <?php displayMessage(); ?>
        
        <h1><?php echo $pageTitle; ?></h1>
        
        <?php if ($query): ?>
            <p>Showing <?php echo count($products); ?> of <?php echo $totalResults; ?> results for "<?php echo sanitizeInput($query); ?>"</p>
        <?php endif; ?>
        
        <!-- Filters -->
        <div class="filters" style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin: 20px 0;">
            <form method="GET" action="search.php">
                <input type="hidden" name="q" value="<?php echo sanitizeInput($query); ?>">
                <div class="grid grid-4">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="input">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitizeInput($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Brand</label>
                        <select name="brand" class="input">
                            <option value="">All Brands</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?php echo $b['id']; ?>" <?php echo $brand == $b['id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitizeInput($b['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Min Price</label>
                        <input type="number" name="min_price" class="input" value="<?php echo $minPrice; ?>" min="0" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>Max Price</label>
                        <input type="number" name="max_price" class="input" value="<?php echo $maxPrice; ?>" min="0" step="0.01">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="search.php" class="btn btn-outline">Clear All</a>
            </form>
        </div>
        
        <!-- Products -->
        <?php if (empty($products)): ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 12px; border: 1px solid #e2e8f0;">
                <i class="fas fa-search" style="font-size: 48px; color: #cbd5e1; margin-bottom: 16px;"></i>
                <h3>No products found</h3>
                <p>Try adjusting your search criteria or browse all products.</p>
                <a href="products.php" class="btn btn-primary">Browse All Products</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <a href="product.php?id=<?php echo $product['id']; ?>">
                                <img src="<?php echo getProductImageUrl($product['main_image']); ?>" 
                                     alt="<?php echo sanitizeInput($product['name']); ?>" loading="lazy">
                            </a>
                            <?php if ($product['sale_price']): ?>
                                <span class="sale-badge">Sale</span>
                            <?php endif; ?>
                            <div class="product-actions">
                                <button class="btn-add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                                <button class="btn-wishlist" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-quick-view">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3><a href="product.php?id=<?php echo $product['id']; ?>"><?php echo sanitizeInput($product['name']); ?></a></h3>
                            <p class="product-brand"><?php echo sanitizeInput($product['brand_name']); ?></p>
                            <div class="product-specs">
                                <span class="hp"><?php echo $product['horsepower']; ?>HP</span>
                                <span class="stroke"><?php echo ucfirst($product['stroke']); ?></span>
                            </div>
                            <div class="product-price">
                                <?php if ($product['sale_price']): ?>
                                    <span class="original-price"><?php echo formatPriceSafe($product['price']); ?></span>
                                    <span class="sale-price"><?php echo formatPriceSafe($product['sale_price']); ?></span>
                                <?php else: ?>
                                    <span class="price"><?php echo formatPriceSafe($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination" style="display: flex; justify-content: center; gap: 8px; margin: 40px 0;">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn btn-outline">← Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                           class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn btn-outline">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
