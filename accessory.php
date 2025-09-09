<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = getProductById($id);
if (!$product) { showMessage('Product not found', 'error'); redirect('products.php'); }
$images = getProductImages($id);

$pageTitle = sanitizeInput($product['name']);
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
            <div class="logo"><a href="index.php"><h1><i class="fas fa-anchor"></i> <?php echo SITE_NAME; ?></h1></a></div>
            <div class="search-bar">
                <form action="products.php" method="GET" class="search-form">
                    <input type="text" name="q" placeholder="Search...">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="cart-info">
                <a href="cart.php" class="cart-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo getCartItemCount(isLoggedIn() ? $_SESSION['user_id'] : null); ?></span>
                    <span class="cart-total"><?php echo formatPrice(getCartTotal(isLoggedIn() ? $_SESSION['user_id'] : null)); ?></span>
                </a>
            </div>
        </div>
        <nav class="navigation">
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a class="active" href="products.php">Products</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            <div class="mobile-menu-toggle"><i class="fas fa-bars"></i></div>
        </nav>
    </div>
</header>

<main class="container">
    <div class="product-page">
        <div class="product-gallery">
            <img src="<?php echo getProductImageUrl($product['main_image']); ?>" alt="<?php echo sanitizeInput($product['name']); ?>">
            <?php if ($images): ?>
                <div class="grid grid-4" style="margin-top:12px;">
                    <?php foreach ($images as $img): ?>
                        <img style="border:1px solid #e2e8f0; border-radius:8px;" src="<?php echo getProductImageUrl($img['image_path']); ?>" alt="<?php echo sanitizeInput($img['alt_text'] ?? $product['name']); ?>">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="product-summary">
            <h2 class="title"><?php echo sanitizeInput($product['name']); ?></h2>
            <div class="meta">Brand: <?php echo sanitizeInput($product['brand_name']); ?> • Model: <?php echo sanitizeInput($product['model']); ?></div>
            <div class="price">
                <?php if ($product['sale_price']): ?>
                    <span class="original-price" style="text-decoration:line-through; color:#64748b; font-size:16px; margin-right:8px;">
              <?php echo formatPrice($product['price']); ?>
            </span>
                    <span><?php echo formatPrice($product['sale_price']); ?></span>
                <?php else: ?>
                    <span><?php echo formatPrice($product['price']); ?></span>
                <?php endif; ?>
            </div>
            <div>
                <strong>Specifications</strong>
                <ul>
                    <li>Horsepower: <?php echo (int)$product['horsepower']; ?> HP</li>
                    <li>Stroke: <?php echo ucfirst($product['stroke']); ?></li>
                    <li>Fuel: <?php echo ucfirst($product['fuel_type']); ?></li>
                    <li>Shaft Length: <?php echo ucwords(str_replace('-', ' ', $product['shaft_length'])); ?></li>
                    <?php if ($product['weight']): ?><li>Weight: <?php echo formatWeight($product['weight']); ?></li><?php endif; ?>
                    <?php if ($product['displacement']): ?><li>Displacement: <?php echo $product['displacement']; ?> cc</li><?php endif; ?>
                    <?php if ($product['cylinders']): ?><li>Cylinders: <?php echo (int)$product['cylinders']; ?></li><?php endif; ?>
                </ul>
            </div>
            <?php if (!empty($product['description'])): ?>
                <div>
                    <strong>Description</strong>
                    <p style="color:#475569;"><?php echo nl2br(sanitizeInput($product['description'])); ?></p>
                </div>
            <?php endif; ?>
            <div class="add-to-cart-section" style="display:flex; gap:10px; align-items:center;">
                <input id="quantity-input" class="input qty" type="number" value="1" min="1" max="99">
                <button id="add-to-cart-btn" class="btn btn-primary" data-product-id="<?php echo $product['id']; ?>">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>
            </div>

            <!-- Fallback form for users with JavaScript disabled -->
            <noscript>
                <form method="POST" action="add_to_cart.php" style="display:flex; gap:10px; align-items:center; margin-top:10px;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input class="input qty" type="number" name="quantity" value="1" min="1" max="99">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-cart-plus"></i> Add to Cart</button>
                </form>
            </noscript>
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

<script src="js/main.js"></script>
</body>
</html>
