<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get featured products
$featuredProducts = getFeaturedProducts(6);
$categories = getAllCategories();
$brands = getAllBrands();

$pageTitle = "Home - Premium Outboard Motors";
$csrfToken = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="Shop premium outboard motors from top brands like Yamaha, Mercury, Honda, and Suzuki. Find the perfect outboard motor for your boat.">
    <meta name="keywords" content="outboard motors, boat engines, marine engines, Yamaha, Mercury, Honda, Suzuki">
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo $csrfToken; ?>">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="contact-info">
                    <span><i class="fas fa-phone"></i> (555) 123-4567</span>
                    <span><i class="fas fa-envelope"></i> info@outboardmotorspro.com</span>
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
                        <input type="text" name="q" placeholder="Search outboard motors..." value="<?php echo isset($_GET['q']) ? sanitizeInput($_GET['q']) : ''; ?>">
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
                    <li><a href="index.php" class="active"><i class="fas fa-home"></i> Home</a></li>
                    <li class="dropdown">
                        <a href="products.php"><i class="fas fa-cog"></i> Products <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <?php foreach ($categories as $category): ?>
                                <li><a href="products.php?category=<?php echo $category['id']; ?>"><?php echo sanitizeInput($category['name']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a href="accessories.php"><i class="fas fa-tools"></i> Accessories</a></li>
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
        
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h2>Premium Outboard Motors</h2>
                <p>Discover the perfect outboard motor for your boat. Quality engines from trusted brands.</p>
                <a href="products.php" class="btn btn-primary">Shop Now</a>
            </div>
            <div class="hero-image">
                <img src="images/hero-outboard.jpg" alt="Premium Outboard Motors" loading="lazy">
            </div>
        </section>
        
        <!-- Featured Categories -->
        <section class="featured-categories">
            <div class="container">
                <h2 class="section-title">Shop by Category</h2>
                <div class="categories-grid">
                    <?php foreach (array_slice($categories, 0, 4) as $category): ?>
                        <div class="category-card">
                            <a href="products.php?category=<?php echo $category['id']; ?>">
                                <div class="category-image">
                                    <img src="<?php echo $category['image'] ? 'uploads/categories/' . $category['image'] : 'images/category-placeholder.jpg'; ?>" 
                                         alt="<?php echo sanitizeInput($category['name']); ?>" loading="lazy">
                                </div>
                                <h3><?php echo sanitizeInput($category['name']); ?></h3>
                                <p><?php echo sanitizeInput($category['description']); ?></p>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        
        <!-- Featured Products -->
        <section class="featured-products">
            <div class="container">
                <h2 class="section-title">Featured Products</h2>
                <div class="products-grid">
                    <?php foreach ($featuredProducts as $product): ?>
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
                                        <span class="original-price"><?php echo formatPrice($product['price']); ?></span>
                                        <span class="sale-price"><?php echo formatPrice($product['sale_price']); ?></span>
                                    <?php else: ?>
                                        <span class="price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-rating">
                                    <div class="stars">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="rating-count">(24 reviews)</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center">
                    <a href="products.php" class="btn btn-outline">View All Products</a>
                </div>
            </div>
        </section>
        
        <!-- Features Section -->
        <section class="features">
            <div class="container">
                <div class="features-grid">
                    <div class="feature">
                        <i class="fas fa-shipping-fast"></i>
                        <h3>Free Shipping</h3>
                        <p>Free shipping on orders over $1,000</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-tools"></i>
                        <h3>Expert Service</h3>
                        <p>Professional installation and maintenance</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Warranty</h3>
                        <p>Comprehensive warranty on all products</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-headset"></i>
                        <h3>Support</h3>
                        <p>24/7 customer support and assistance</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Newsletter -->
        <section class="newsletter">
            <div class="container">
                <div class="newsletter-content">
                    <h2>Stay Updated</h2>
                    <p>Subscribe to our newsletter for the latest products and special offers</p>
                    <form class="newsletter-form" id="newsletter-form">
                        <input type="email" name="email" placeholder="Enter your email address" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
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
                    <p>Your trusted source for premium outboard motors. We offer the best selection of marine engines from top brands.</p>
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
                        <li><a href="brands.php">Brands</a></li>
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
