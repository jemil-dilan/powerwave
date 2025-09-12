<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$categories = getAllCategories();
$brands = getAllBrands();

$pageTitle = 'Warranty Information';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description"
        content="Learn about warranty coverage for outboard motors and marine equipment. Comprehensive warranty protection on all products.">
    <meta name="keywords" content="outboard motor warranty, marine engine warranty, warranty coverage, warranty terms">

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
                        <input type="text" name="q" placeholder="Search outboard motors...">
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
                            class="cart-total"><?php echo getCartTotalForDisplay(isLoggedIn() ? $_SESSION['user_id'] : null); ?></span>
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
                    <li><a href="accessories.php"><i class="fas fa-tools"></i> Accessories</a></li>
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

        <!-- Page Header -->
        <section class="page-header">
            <div class="container">
                <div class="header-content">
                    <h1>Warranty Information</h1>
                    <p>Comprehensive warranty coverage on all outboard motors and marine equipment. Your investment is
                        protected with industry-leading warranty terms.</p>
                </div>
            </div>
        </section>

        <!-- Warranty Content -->
        <section class="policy-section">
            <div class="container">
                <div class="policy-content">

                    <!-- Overview -->
                    <div class="policy-card">
                        <div class="policy-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="policy-text">
                            <h2>Industry-Leading Warranty Protection</h2>
                            <p>We stand behind every product we sell with comprehensive warranty coverage that exceeds
                                industry standards. Your peace of mind is our priority.</p>
                        </div>
                    </div>

                    <!-- Warranty Coverage -->
                    <div class="content-section">
                        <h2>Warranty Coverage by Product Type</h2>
                        <div class="info-grid">
                            <div class="info-card">
                                <h3><i class="fas fa-cog"></i> New Outboard Motors</h3>
                                <ul>
                                    <li><strong>3-Year Limited Warranty</strong> on all new outboard motors</li>
                                    <li>Covers manufacturing defects and material failures</li>
                                    <li>Includes parts and labor for authorized repairs</li>
                                    <li>Transferable to subsequent owners</li>
                                </ul>
                            </div>
                            <div class="info-card">
                                <h3><i class="fas fa-tools"></i> Marine Accessories</h3>
                                <ul>
                                    <li><strong>1-Year Limited Warranty</strong> on accessories</li>
                                    <li>Covers propellers, controls, and rigging</li>
                                    <li>Manufacturing defect protection</li>
                                    <li>Free replacement for defective items</li>
                                </ul>
                            </div>
                            <div class="info-card">
                                <h3><i class="fas fa-recycle"></i> Certified Pre-Owned</h3>
                                <ul>
                                    <li><strong>6-Month Limited Warranty</strong> on used motors</li>
                                    <li>Comprehensive inspection before sale</li>
                                    <li>Major component coverage included</li>
                                    <li>Optional extended warranty available</li>
                                </ul>
                            </div>
                            <div class="info-card">
                                <h3><i class="fas fa-plus"></i> Extended Warranty</h3>
                                <ul>
                                    <li><strong>Up to 7 Years</strong> total coverage available</li>
                                    <li>Comprehensive coverage beyond standard warranty</li>
                                    <li>Nationwide service network</li>
                                    <li>24/7 roadside assistance included</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- What's Covered -->
                    <div class="content-section">
                        <h2>What's Covered</h2>
                        <div class="grid grid-2">
                            <div class="condition-card">
                                <h3><i class="fas fa-check-circle"></i> Covered Under Warranty</h3>
                                <ul>
                                    <li>Manufacturing defects in materials or workmanship</li>
                                    <li>Engine block and internal components</li>
                                    <li>Electrical system failures</li>
                                    <li>Fuel system components</li>
                                    <li>Cooling system components</li>
                                    <li>Ignition system defects</li>
                                    <li>Factory-installed accessories</li>
                                </ul>
                            </div>
                            <div class="condition-card">
                                <h3><i class="fas fa-times-circle"></i> Not Covered</h3>
                                <ul>
                                    <li>Normal wear and tear items (spark plugs, filters, impellers)</li>
                                    <li>Damage from improper installation</li>
                                    <li>Damage from contaminated fuel</li>
                                    <li>Accident or impact damage</li>
                                    <li>Corrosion from lack of maintenance</li>
                                    <li>Commercial or rental use (special terms apply)</li>
                                    <li>Modifications or aftermarket parts</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Warranty Registration -->
                    <div class="content-section">
                        <h2>Warranty Registration</h2>
                        <p>To ensure your warranty coverage, please register your product within 30 days of purchase.
                        </p>

                        <div class="process-steps">
                            <div class="step">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h3>Keep Your Documentation</h3>
                                    <p>Save your original purchase receipt and all product documentation. You'll need
                                        these for warranty claims.</p>
                                </div>
                            </div>
                            <div class="step">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h3>Register Online</h3>
                                    <p>Visit the manufacturer's website or call us to register your product. This
                                        activates your warranty coverage.</p>
                                </div>
                            </div>
                            <div class="step">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h3>Schedule Installation</h3>
                                    <p>Use only authorized dealers for installation to maintain warranty coverage. We
                                        provide professional installation services.</p>
                                </div>
                            </div>
                            <div class="step">
                                <div class="step-number">4</div>
                                <div class="step-content">
                                    <h3>Follow Maintenance Schedule</h3>
                                    <p>Regular maintenance is required to keep your warranty valid. We offer
                                        full-service maintenance programs.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Warranty Claims -->
                    <div class="content-section">
                        <h2>Making a Warranty Claim</h2>
                        <p>If you experience a covered issue, we make the warranty claim process as simple as possible.
                        </p>

                        <div class="highlight-box">
                            <h3><i class="fas fa-clipboard-list"></i> Warranty Claim Process</h3>
                            <ol>
                                <li>Contact our service department at (555) 123-4567</li>
                                <li>Provide your product serial number and purchase information</li>
                                <li>Describe the issue you're experiencing</li>
                                <li>Schedule an inspection at our facility or authorized service center</li>
                                <li>We'll handle all manufacturer communications</li>
                                <li>Receive repair or replacement at no cost to you</li>
                            </ol>
                        </div>

                        <div class="info-grid">
                            <div class="info-card">
                                <h3><i class="fas fa-clock"></i> Response Time</h3>
                                <p>We respond to warranty claims within 24 hours and aim to resolve issues within 5-7
                                    business days.</p>
                            </div>
                            <div class="info-card">
                                <h3><i class="fas fa-map-marker-alt"></i> Service Locations</h3>
                                <p>Nationwide network of authorized service centers for convenient warranty service.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Maintenance Requirements -->
                    <div class="content-section">
                        <h2>Maintenance Requirements</h2>
                        <p>Regular maintenance is essential to keep your warranty valid and your motor running at peak
                            performance.</p>

                        <div class="grid grid-2">
                            <div class="condition-card">
                                <h3><i class="fas fa-calendar-alt"></i> Required Maintenance</h3>
                                <ul>
                                    <li><strong>Every 100 Hours or Annually:</strong> Oil change, filter replacement,
                                        spark plug inspection</li>
                                    <li><strong>Every 300 Hours:</strong> Impeller replacement, thermostat inspection
                                    </li>
                                    <li><strong>Seasonally:</strong> Winterization/de-winterization service</li>
                                    <li><strong>As Needed:</strong> Propeller inspection and replacement</li>
                                </ul>
                            </div>
                            <div class="condition-card">
                                <h3><i class="fas fa-wrench"></i> Service Records</h3>
                                <ul>
                                    <li>Keep detailed records of all maintenance</li>
                                    <li>Use only genuine OEM parts</li>
                                    <li>Have service performed by authorized technicians</li>
                                    <li>Follow manufacturer's maintenance schedule</li>
                                </ul>
                            </div>
                        </div>

                        <div class="emergency-contact">
                            <h3><i class="fas fa-exclamation-triangle"></i> Warranty Maintenance Tips</h3>
                            <p>To protect your warranty coverage:</p>
                            <ul>
                                <li>Never skip scheduled maintenance intervals</li>
                                <li>Use only recommended oils and fluids</li>
                                <li>Flush with fresh water after every saltwater use</li>
                                <li>Store properly during off-season</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Extended Warranty -->
                    <div class="content-section">
                        <h2>Extended Warranty Options</h2>
                        <p>Protect your investment beyond the standard warranty period with our extended warranty plans.
                        </p>

                        <div class="info-grid">
                            <div class="info-card">
                                <h3><i class="fas fa-shield-alt"></i> PowerGuard Plus</h3>
                                <p><strong>5-Year Total Coverage</strong><br>
                                    Extends your warranty to 5 years total coverage with comprehensive protection
                                    including mechanical breakdown coverage.</p>
                                <ul style="margin-top: 12px;">
                                    <li>Covers all major components</li>
                                    <li>No deductible required</li>
                                    <li>Transferable coverage</li>
                                </ul>
                            </div>
                            <div class="info-card">
                                <h3><i class="fas fa-crown"></i> PowerGuard Elite</h3>
                                <p><strong>7-Year Total Coverage</strong><br>
                                    Our most comprehensive coverage with additional benefits including annual
                                    maintenance services.</p>
                                <ul style="margin-top: 12px;">
                                    <li>Everything in PowerGuard Plus</li>
                                    <li>Annual maintenance included</li>
                                    <li>Priority service scheduling</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Warranty Support -->
                    <div class="contact-section">
                        <h2>Warranty Support</h2>
                        <p>Our dedicated warranty department is here to help with all your warranty needs and questions.
                        </p>

                        <div class="contact-options">
                            <div class="contact-option">
                                <i class="fas fa-phone"></i>
                                <div>
                                    <h4>Warranty Hotline</h4>
                                    <p>(555) 123-4567 ext. 2<br>Mon-Fri: 7AM-7PM EST</p>
                                </div>
                            </div>
                            <div class="contact-option">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <h4>Email Support</h4>
                                    <p>warranty@outboardmotorspro.com<br>Response within 4 hours</p>
                                </div>
                            </div>
                            <div class="contact-option">
                                <i class="fas fa-tools"></i>
                                <div>
                                    <h4>Service Center</h4>
                                    <p>123 Marina Drive<br>Full-service facility</p>
                                </div>
                            </div>
                        </div>

                        <div style="text-align: center; margin-top: 32px;">
                            <a href="contact.php" class="btn btn-primary">Contact Warranty Department</a>
                            <a href="products.php" class="btn btn-outline" style="margin-left: 8px;">Shop Products</a>
                        </div>
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
                    <p>Your trusted source for premium outboard motors. We offer the best selection of marine engines
                        from top brands.</p>
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
</body>

</html>