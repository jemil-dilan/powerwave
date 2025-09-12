<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$db = Database::getInstance();

// Fetch the ID for the 'Accessories' category
$accessoriesCategory = $db->fetchOne("SELECT id FROM categories WHERE name = ?", ['Accessories']);
$accessoriesCategoryId = $accessoriesCategory ? $accessoriesCategory['id'] : 0;

// Fetch all brands that have accessories
$brandsWithAccessories = $db->fetchAll(
    "SELECT DISTINCT b.id, b.name FROM brands b JOIN products p ON b.id = p.brand_id WHERE p.category_id = ? ORDER BY b.name", 
    [$accessoriesCategoryId]
);

// Get the selected brand from the URL, default to 'All'
$selectedBrand = isset($_GET['brand']) ? (int)$_GET['brand'] : 'All';

// Build the query to fetch accessories from the products table
$sql = "SELECT p.*, b.name as brand_name FROM products p JOIN brands b ON p.brand_id = b.id WHERE p.status = 'active' AND p.category_id = ?";
$params = [$accessoriesCategoryId];

if ($selectedBrand !== 'All') {
    $sql .= " AND p.brand_id = ?";
    $params[] = $selectedBrand;
}

$sql .= " ORDER BY p.name";

$accessories = $db->fetchAll($sql, $params);

$pageTitle = "Marine Accessories - WaveMaster Outboards";

// These are needed for the header dropdowns
$categories = getAllCategories();
$brands = getAllBrands();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description"
        content="Shop premium marine accessories for your WaveMaster outboard motor. Propellers, maintenance products, covers, electronics and more.">
    <meta name="keywords"
        content="marine accessories, outboard parts, propellers, engine oil, motor covers, marine electronics">

    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="contact-info">
                    <span><i class="fas fa-phone"></i> (555) 123-4567</span>
                    <span><i class="fas fa-envelope"></i> <?php echo SITE_EMAIL; ?></span>
                </div>
                <div class="user-actions">
                    <?php if (isLoggedIn()): ?>
                        <?php $currentUser = getCurrentUser(); ?>
                        <span>Welcome, <?php echo sanitizeInput($currentUser['first_name']); ?>!</span>
                        <a href="account.php"><i class="fas fa-user"></i> My Account</a>
                        <?php if (isAdmin()): ?>
                            <a href="admin/"><i class="fas fa-cog"></i> Admin</a>
                        <?php endif; ?>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php else: ?>
                        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main Header -->
            <div class="main-header">
                <div class="logo">
                    <a href="index.php">
                        <img src="wave.jpeg" alt="<?php echo SITE_NAME; ?>" style="height: 50px; width: auto;">
                        <h1><?php echo SITE_NAME; ?></h1>
                    </a>
                </div>

                <!-- Search Bar -->
                <div class="search-bar">
                    <form action="search.php" method="GET" class="search-form">
                        <input type="text" name="q" placeholder="Search accessories...">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>

                <!-- Cart -->
                <div class="cart-info">
                    <a href="cart.php" class="cart-link">
                        <i class="fas fa-shopping-cart"></i>
                        <span
                            class="cart-count"><?php echo getCartItemCount(isLoggedIn() ? $_SESSION['user_id'] : null); ?></span>
                        <span
                            class="cart-total"><?php echo formatPrice(getCartTotal(isLoggedIn() ? $_SESSION['user_id'] : null)); ?></span>
                    </a>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="navigation">
                <ul class="nav-menu">
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li class="dropdown">
                        <a href="products.php"><i class="fas fa-cog"></i> Products <i
                                class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <?php foreach ($categories as $category): ?>
                                <li><a
                                        href="products.php?category=<?php echo $category['id']; ?>"><?php echo sanitizeInput($category['name']); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a href="accessories.php" class="active"><i class="fas fa-tools"></i> Accessories</a></li>
                    <li class="dropdown">
                        <a href="brands.php"><i class="fas fa-tags"></i> Brands <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <?php foreach ($brands as $brand): ?>
                                <li><a
                                        href="products.php?brand=<?php echo $brand['id']; ?>"><?php echo sanitizeInput($brand['name']); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a href="brand.php"><i class="fas fa-award"></i> Our Brand</a></li>
                    <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
                    <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                </ul>
                <div class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <?php displayMessage(); ?>

        <!-- Accessories Hero Section -->
        <section class="accessories-hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Marine Accessories</h1>
                    <p>Premium accessories and parts to enhance your WaveMaster outboard motor experience. From
                        performance upgrades to essential maintenance items.</p>
                </div>
            </div>
        </section>

        <!-- Brand Filter -->
        <section class="accessories-filter">
            <div class="container">
                <div class="filter-tabs">
<<<<<<< HEAD
                    <?php foreach ($accessoryCategories as $catKey => $catName): ?>
                        <a href="accessories.php?category=<?php echo $catKey; ?>"
                            class="filter-tab <?php echo $selectedCategory === $catKey ? 'active' : ''; ?>">
                            <?php echo $catName; ?>
=======
                    <a href="accessories.php?brand=All" class="filter-tab <?php echo $selectedBrand === 'All' ? 'active' : ''; ?>">All Brands</a>
                    <?php foreach ($brandsWithAccessories as $brand): ?>
                        <a href="accessories.php?brand=<?php echo urlencode($brand['id']); ?>"
                           class="filter-tab <?php echo $selectedBrand === (int)$brand['id'] ? 'active' : ''; ?>">
                            <?php echo sanitizeInput($brand['name']); ?>
>>>>>>> 8afeb0f115d9f9bda8e9ac3e5eb63e5f16c27d2c
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <div id="add-to-cart-message" class="container" style="margin-top: 15px; margin-bottom: 15px;"></div>

        <!-- Accessories Grid -->
        <section class="accessories-products">
            <div class="container">
                <div class="products-grid">
                    <?php if (empty($accessories)): ?>
                        <div style="text-align: center; padding: 40px; background: white; border-radius: 12px; border: 1px solid #e2e8f0; grid-column: 1 / -1;">
                            <p>No accessories found in this category.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($accessories as $accessory): ?>
                            <div class="product-card">
                                <div class="product-image">
<<<<<<< HEAD
                                    <img src="<?php echo $accessory['image']; ?>" alt="<?php echo $accessory['name']; ?>"
                                        loading="lazy">
=======
                                    <a href="product.php?id=<?php echo $accessory['id']; ?>">
                                        <img src="<?php echo getProductImageUrl($accessory['image']); ?>" alt="<?php echo sanitizeInput($accessory['name']); ?>" loading="lazy">
                                    </a>
>>>>>>> 8afeb0f115d9f9bda8e9ac3e5eb63e5f16c27d2c
                                    <?php if ($accessory['sale_price']): ?>
                                        <span class="sale-badge">Sale</span>
                                    <?php endif; ?>
                                    <div class="product-actions">
                                        <button class="btn-add-to-cart" data-product-id="<?php echo $accessory['id']; ?>"><i class="fas fa-cart-plus"></i></button>
                                        <a class="btn-quick-view" href="product.php?id=<?php echo $accessory['id']; ?>"><i class="fas fa-eye"></i></a>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h3><a href="product.php?id=<?php echo $accessory['id']; ?>"><?php echo sanitizeInput($accessory['name']); ?></a></h3>
                                    <p class="product-brand"><?php echo sanitizeInput($accessory['brand_name']); ?></p>
                                    <div class="product-specs"><span><?php echo (int)$accessory['horsepower']; ?>HP</span> <span><?php echo ucfirst($accessory['stroke']); ?></span></div>
                                    <div class="product-price">
                                        <?php if ($accessory['sale_price']): ?>
                                            <span class="original-price"><?php echo formatPrice($accessory['price']); ?></span>
                                            <span class="sale-price"><?php echo formatPrice($accessory['sale_price']); ?></span>
                                        <?php else: ?>
                                            <span class="price"><?php echo formatPrice($accessory['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
<<<<<<< HEAD
                                    <div class="product-rating">
                                        <div class="stars">
                                            <?php
                                            $rating = $accessory['rating'];
                                            $fullStars = floor($rating);
                                            $hasHalfStar = $rating - $fullStars >= 0.5;

                                            for ($i = 0; $i < $fullStars; $i++): ?>
                                                <i class="fas fa-star"></i>
                                            <?php endfor;

                                            if ($hasHalfStar): ?>
                                                <i class="fas fa-star-half-alt"></i>
                                            <?php endif;

                                            for ($i = $fullStars + ($hasHalfStar ? 1 : 0); $i < 5; $i++): ?>
                                                <i class="far fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-count">(<?php echo rand(15, 50); ?> reviews)</span>
                                    </div>
=======
>>>>>>> 8afeb0f115d9f9bda8e9ac3e5eb63e5f16c27d2c
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Accessories Info Section -->
        <section class="accessories-info">
            <div class="container">
                <div class="info-grid">
                    <div class="info-card">
                        <i class="fas fa-medal"></i>
                        <h3>Genuine Parts</h3>
                        <p>All accessories are genuine WaveMaster parts designed specifically for optimal performance
                            and compatibility.</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-shipping-fast"></i>
                        <h3>Fast Shipping</h3>
                        <p>Quick delivery on all accessories with free shipping on orders over $75. Most items ship
                            within 24 hours.</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-tools"></i>
                        <h3>Expert Support</h3>
                        <p>Need installation help? Our certified technicians provide expert guidance and support for all
                            accessories.</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Quality Guarantee</h3>
                        <p>Every accessory comes with our quality guarantee and manufacturer warranty for your peace of
                            mind.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo SITE_NAME; ?></h3>
                    <p>Your trusted source for premium outboard motors and marine accessories. Quality products with
                        unmatched reliability.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="products.php">All Products</a></li>
                        <li><a href="accessories.php">Accessories</a></li>
                        <li><a href="brand.php">Our Brand</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Categories</h4>
                    <ul>
                        <?php foreach (array_slice($categories, 0, 4) as $category): ?>
                            <li><a
                                    href="products.php?category=<?php echo $category['id']; ?>"><?php echo sanitizeInput($category['name']); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Customer Service</h4>
                    <ul>
                        <li><a href="shipping.php">Shipping Info</a></li>
                        <li><a href="returns.php">Returns</a></li>
                        <li><a href="warranty.php">Warranty</a></li>
                        <li><a href="faq.php">FAQ</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <div class="contact-info">
                        <p><i class="fas fa-phone"></i> (555) 123-4567</p>
                        <p><i class="fas fa-envelope"></i> <?php echo SITE_EMAIL; ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> 4801 W Buckeye Rd<br>Phoenix, AZ 85043</p>
                        <p><i class="fas fa-clock"></i> Mon-Fri: 8AM-6PM<br>Sat: 9AM-5PM, Sun: Closed</p>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                <div class="footer-links">
                    <a href="privacy.php">Privacy Policy</a>
                    <a href="terms.php">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('.add-to-cart-form');
        const messageDiv = document.getElementById('add-to-cart-message');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                formData.append('csrf_token', csrfToken);

                const button = form.querySelector('button[type="submit"]');
                const originalButtonIcon = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': csrfToken
                    },
                    body: new URLSearchParams(formData).toString()
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cartCountEl = document.querySelector('.cart-count');
                        const cartTotalEl = document.querySelector('.cart-total');
                        if (cartCountEl) cartCountEl.textContent = data.cart_count;
                        if (cartTotalEl) cartTotalEl.textContent = data.cart_total_formatted;

                        messageDiv.innerHTML = '<div class="alert alert-success">Accessory added to cart!</div>';
                        setTimeout(() => messageDiv.innerHTML = '', 3000);
                    } else {
                        messageDiv.innerHTML = `<div class="alert alert-error">Error: ${data.message}</div>`;
                        setTimeout(() => messageDiv.innerHTML = '', 5000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    messageDiv.innerHTML = '<div class="alert alert-error">An unexpected error occurred.</div>';
                    setTimeout(() => messageDiv.innerHTML = '', 5000);
                })
                .finally(() => {
                    button.disabled = false;
                    button.innerHTML = originalButtonIcon;
                });
            });
        });
    });
  </script>
</body>

</html>