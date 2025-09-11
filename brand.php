<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$categories = getAllCategories();
$brands = getAllBrands();

$pageTitle = "Our Brand - WaveMaster Outboards";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="Discover WaveMaster Outboards - Leading manufacturer of premium marine engines. Innovation, reliability, and performance for over 50 years.">
    <meta name="keywords" content="WaveMaster Outboards, marine engines, boat motors, outboard technology, marine innovation">
    
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
                        <img src="wave.jpeg" alt="<?php echo SITE_NAME; ?>" style="height: 60px; width: auto; border-radius: 10px;">
                        <h1><?php echo SITE_NAME; ?></h1>
                    </a>
                </div>
                
                <!-- Search Bar -->
                <div class="search-bar">
                    <form action="search.php" method="GET" class="search-form">
                        <input type="text" name="q" placeholder="Search outboard motors...">
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
                    <li class="dropdown">
                        <a href="brands.php"><i class="fas fa-tags"></i> Brands <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <?php foreach ($brands as $brand): ?>
                                <li><a href="products.php?brand=<?php echo $brand['id']; ?>"><?php echo sanitizeInput($brand['name']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a href="brand.php" class="active"><i class="fas fa-award"></i> Our Brand</a></li>
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
        
        <!-- Brand Hero Section -->
        <section class="brand-hero">
            <div class="hero-content">
                <div class="container">
                    <div class="hero-text">
                        <h1>WaveMaster Outboards</h1>
                        <h2>Mastering the Waves Since 1970</h2>
                        <p>For over five decades, WaveMaster Outboards has been at the forefront of marine propulsion innovation, crafting outboard motors that combine cutting-edge technology with unmatched reliability.</p>
                        <a href="products.php" class="btn btn-primary">Explore Our Motors</a>
                    </div>
                    <div class="hero-image">
                        <img src="images/hero-outboard.jpg" alt="WaveMaster Outboard Motors" loading="lazy">
                    </div>
                </div>
            </div>
        </section>

        <!-- Brand Story Section -->
        <section class="brand-story">
            <div class="container">
                <div class="story-content">
                    <div class="story-text">
                        <h2>Our Story</h2>
                        <p>Founded in 1970 by marine engineering pioneer Captain James WaveMaster, our company began with a simple mission: to create the most reliable and efficient outboard motors for passionate boaters worldwide.</p>
                        <p>What started in a small workshop in Miami has grown into a global leader in marine propulsion technology. Today, WaveMaster Outboards power vessels in over 80 countries, from weekend fishing trips to professional racing circuits.</p>
                        <p>Our commitment to innovation has led to groundbreaking developments in fuel efficiency, emissions reduction, and digital integration, making us the preferred choice for both recreational boaters and marine professionals.</p>
                    </div>
                    <div class="story-stats">
                        <div class="stat">
                            <h3>50+</h3>
                            <p>Years of Excellence</p>
                        </div>
                        <div class="stat">
                            <h3>2M+</h3>
                            <p>Motors Worldwide</p>
                        </div>
                        <div class="stat">
                            <h3>80+</h3>
                            <p>Countries Served</p>
                        </div>
                        <div class="stat">
                            <h3>#1</h3>
                            <p>Customer Satisfaction</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Brand Values Section -->
        <section class="brand-values">
            <div class="container">
                <h2 class="section-title">Our Core Values</h2>
                <div class="values-grid">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h3>Innovation</h3>
                        <p>We continuously push the boundaries of marine technology, investing heavily in R&D to bring you the most advanced outboard motors on the market.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Reliability</h3>
                        <p>Every WaveMaster motor is built to last, with rigorous testing and quality control ensuring dependable performance in any conditions.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h3>Sustainability</h3>
                        <p>We're committed to protecting the waters we love, developing eco-friendly technologies that reduce emissions and environmental impact.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Community</h3>
                        <p>We support boating communities worldwide through sponsorships, education programs, and partnerships with marine conservation organizations.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Technology Section -->
        <section class="brand-technology">
            <div class="container">
                <div class="tech-content">
                    <div class="tech-text">
                        <h2>Cutting-Edge Technology</h2>
                        <h3>WaveMaster Advanced Propulsion System (WMAPS)</h3>
                        <p>Our proprietary WMAPS technology optimizes fuel injection, ignition timing, and propeller design to deliver:</p>
                        <ul>
                            <li><i class="fas fa-check"></i> Up to 30% better fuel efficiency</li>
                            <li><i class="fas fa-check"></i> 40% reduction in harmful emissions</li>
                            <li><i class="fas fa-check"></i> Whisper-quiet operation</li>
                            <li><i class="fas fa-check"></i> Smart diagnostics and monitoring</li>
                            <li><i class="fas fa-check"></i> Seamless smartphone integration</li>
                        </ul>
                        <a href="products.php" class="btn btn-outline">Discover Our Technology</a>
                    </div>
                    <div class="tech-features">
                        <div class="feature-highlight">
                            <i class="fas fa-microchip"></i>
                            <h4>Smart Engine Management</h4>
                            <p>AI-powered systems optimize performance in real-time</p>
                        </div>
                        <div class="feature-highlight">
                            <i class="fas fa-mobile-alt"></i>
                            <h4>WaveMaster Connect App</h4>
                            <p>Monitor and control your motor from your smartphone</p>
                        </div>
                        <div class="feature-highlight">
                            <i class="fas fa-tools"></i>
                            <h4>Self-Diagnostic System</h4>
                            <p>Predictive maintenance alerts prevent costly repairs</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Awards and Recognition -->
        <section class="brand-awards">
            <div class="container">
                <h2 class="section-title">Awards & Recognition</h2>
                <div class="awards-grid">
                    <div class="award">
                        <i class="fas fa-trophy"></i>
                        <h3>Marine Innovation Award 2023</h3>
                        <p>International Marine Technology Association</p>
                    </div>
                    <div class="award">
                        <i class="fas fa-medal"></i>
                        <h3>Best Outboard Motor 2023</h3>
                        <p>Boating Magazine</p>
                    </div>
                    <div class="award">
                        <i class="fas fa-star"></i>
                        <h3>Customer Choice Award</h3>
                        <p>Marine Industry Association</p>
                    </div>
                    <div class="award">
                        <i class="fas fa-leaf"></i>
                        <h3>Green Technology Leader</h3>
                        <p>Environmental Marine Council</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="brand-cta">
            <div class="container">
                <div class="cta-content">
                    <h2>Ready to Experience WaveMaster Excellence?</h2>
                    <p>Join millions of satisfied customers worldwide who trust WaveMaster Outboards for their marine adventures.</p>
                    <div class="cta-buttons">
                        <a href="products.php" class="btn btn-primary">Shop Motors</a>
                        <a href="contact.php" class="btn btn-outline">Find a Dealer</a>
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
                    <p>Your trusted source for premium outboard motors. We offer the best selection of marine engines with cutting-edge technology and unmatched reliability.</p>
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
