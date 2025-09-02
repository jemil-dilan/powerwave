<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$categories = getAllCategories();
$brands = getAllBrands();

$pageTitle = "Marine Accessories - PowerWave outboards";

// Sample accessories data (in a real application, this would come from the database)
$accessories = [
    [
        'id' => 1,
        'name' => 'PowerWave Premium Propeller Set',
        'description' => 'High-performance stainless steel propellers designed for maximum efficiency and durability.',
        'price' => 299.99,
        'sale_price' => null,
        'image' => 'images/accessories/propeller-set.jpg',
        'category' => 'Propellers',
        'rating' => 4.8
    ],
    [
        'id' => 2,
        'name' => 'Marine Engine Oil - Synthetic Blend',
        'description' => 'Premium synthetic blend oil specifically formulated for PowerWave outboard motors.',
        'price' => 89.99,
        'sale_price' => 69.99,
        'image' => 'images/accessories/engine-oil.jpg',
        'category' => 'Maintenance',
        'rating' => 4.9
    ],
    [
        'id' => 3,
        'name' => 'Outboard Motor Cover - Waterproof',
        'description' => 'Heavy-duty waterproof cover to protect your outboard motor from the elements.',
        'price' => 149.99,
        'sale_price' => null,
        'image' => 'images/accessories/motor-cover.jpg',
        'category' => 'Protection',
        'rating' => 4.7
    ],
    [
        'id' => 4,
        'name' => 'PowerWave Digital Gauge Kit',
        'description' => 'Advanced digital gauge system with RPM, fuel flow, and engine diagnostics.',
        'price' => 899.99,
        'sale_price' => null,
        'image' => 'images/accessories/gauge-kit.jpg',
        'category' => 'Electronics',
        'rating' => 4.6
    ],
    [
        'id' => 5,
        'name' => 'Fuel Water Separator Filter',
        'description' => 'Essential fuel system component that removes water and contaminants from fuel.',
        'price' => 45.99,
        'sale_price' => 34.99,
        'image' => 'images/accessories/fuel-filter.jpg',
        'category' => 'Maintenance',
        'rating' => 4.8
    ],
    [
        'id' => 6,
        'name' => 'Outboard Motor Stand - Heavy Duty',
        'description' => 'Adjustable heavy-duty stand for secure storage and maintenance of outboard motors.',
        'price' => 199.99,
        'sale_price' => null,
        'image' => 'images/accessories/motor-stand.jpg',
        'category' => 'Storage',
        'rating' => 4.5
    ],
    [
        'id' => 7,
        'name' => 'PowerWave Remote Control Kit',
        'description' => 'Complete remote control system for convenient motor operation from anywhere on your boat.',
        'price' => 549.99,
        'sale_price' => 499.99,
        'image' => 'images/accessories/remote-control.jpg',
        'category' => 'Controls',
        'rating' => 4.7
    ],
    [
        'id' => 8,
        'name' => 'Stainless Steel Trim Tabs',
        'description' => 'Premium stainless steel trim tabs for improved boat performance and fuel efficiency.',
        'price' => 379.99,
        'sale_price' => null,
        'image' => 'images/accessories/trim-tabs.jpg',
        'category' => 'Performance',
        'rating' => 4.6
    ]
];

$accessoryCategories = [
    'All' => 'All Accessories',
    'Propellers' => 'Propellers',
    'Maintenance' => 'Maintenance',
    'Protection' => 'Protection',
    'Electronics' => 'Electronics',
    'Storage' => 'Storage',
    'Controls' => 'Controls',
    'Performance' => 'Performance'
];

$selectedCategory = isset($_GET['category']) ? $_GET['category'] : 'All';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="Shop premium marine accessories for your PowerWave outboard motor. Propellers, maintenance products, covers, electronics and more.">
    <meta name="keywords" content="marine accessories, outboard parts, propellers, engine oil, motor covers, marine electronics">
    
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
                        <img src="logo1.png" alt="<?php echo SITE_NAME; ?>" style="height: 50px; width: auto;">
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
                        <span class="cart-count"><?php echo getCartItemCount(isLoggedIn() ? $_SESSION['user_id'] : null); ?></span>
                        <span class="cart-total"><?php echo formatPrice(getCartTotal(isLoggedIn() ? $_SESSION['user_id'] : null)); ?></span>
                    </a>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="navigation">
                <ul class="nav-menu">
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li class="dropdown">
                        <a href="products.php"><i class="fas fa-cog"></i> Products <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <?php foreach ($categories as $category): ?>
                                <li><a href="products.php?category=<?php echo $category['id']; ?>"><?php echo sanitizeInput($category['name']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a href="accessories.php" class="active"><i class="fas fa-tools"></i> Accessories</a></li>
                    <li class="dropdown">
                        <a href="brands.php"><i class="fas fa-tags"></i> Brands <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <?php foreach ($brands as $brand): ?>
                                <li><a href="products.php?brand=<?php echo $brand['id']; ?>"><?php echo sanitizeInput($brand['name']); ?></a></li>
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
                    <p>Premium accessories and parts to enhance your PowerWave outboard motor experience. From performance upgrades to essential maintenance items.</p>
                </div>
            </div>
        </section>

        <!-- Category Filter -->
        <section class="accessories-filter">
            <div class="container">
                <div class="filter-tabs">
                    <?php foreach ($accessoryCategories as $catKey => $catName): ?>
                        <a href="accessories.php?category=<?php echo $catKey; ?>" 
                           class="filter-tab <?php echo $selectedCategory === $catKey ? 'active' : ''; ?>">
                            <?php echo $catName; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Accessories Grid -->
        <section class="accessories-products">
            <div class="container">
                <div class="products-grid">
                    <?php foreach ($accessories as $accessory): ?>
                        <?php if ($selectedCategory === 'All' || $selectedCategory === $accessory['category']): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?php echo $accessory['image']; ?>" alt="<?php echo $accessory['name']; ?>" loading="lazy">
                                    <?php if ($accessory['sale_price']): ?>
                                        <span class="sale-badge">Sale</span>
                                    <?php endif; ?>
                                    <div class="product-actions">
                                        <button class="btn-add-to-cart" data-product-id="acc_<?php echo $accessory['id']; ?>">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                        <button class="btn-wishlist" data-product-id="acc_<?php echo $accessory['id']; ?>">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                        <button class="btn-quick-view" data-product-id="acc_<?php echo $accessory['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h3><?php echo $accessory['name']; ?></h3>
                                    <p class="product-category"><?php echo $accessory['category']; ?></p>
                                    <p class="product-description"><?php echo $accessory['description']; ?></p>
                                    <div class="product-price">
                                        <?php if ($accessory['sale_price']): ?>
                                            <span class="original-price"><?php echo formatPrice($accessory['price']); ?></span>
                                            <span class="sale-price"><?php echo formatPrice($accessory['sale_price']); ?></span>
                                        <?php else: ?>
                                            <span class="price"><?php echo formatPrice($accessory['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
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
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
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
                        <p>All accessories are genuine PowerWave parts designed specifically for optimal performance and compatibility.</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-shipping-fast"></i>
                        <h3>Fast Shipping</h3>
                        <p>Quick delivery on all accessories with free shipping on orders over $75. Most items ship within 24 hours.</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-tools"></i>
                        <h3>Expert Support</h3>
                        <p>Need installation help? Our certified technicians provide expert guidance and support for all accessories.</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Quality Guarantee</h3>
                        <p>Every accessory comes with our quality guarantee and manufacturer warranty for your peace of mind.</p>
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
                    <p>Your trusted source for premium outboard motors and marine accessories. Quality products with unmatched reliability.</p>
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
                            <li><a href="products.php?category=<?php echo $category['id']; ?>"><?php echo sanitizeInput($category['name']); ?></a></li>
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
                        <p><i class="fas fa-map-marker-alt"></i> 123 Marina Drive<br>Coastal City, CC 12345</p>
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
</body>
</html>
