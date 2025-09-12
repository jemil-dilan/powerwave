<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = 'About Us';
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
    <link rel="stylesheet" href="css/production-fixes.css">
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
                    <form action="products.php" method="GET" class="search-form">
                        <input type="text" name="q" placeholder="Search...">
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
            <nav class="navigation">
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="about.php" class="active">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
                <div class="mobile-menu-toggle"><i class="fas fa-bars"></i></div>
            </nav>
        </div>
    </header>

    <main class="container">
        <div style="max-width: 800px; margin: 0 auto;">
            <h1>About <?php echo SITE_NAME; ?></h1>
            
            <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 32px; margin: 24px 0;">
                <h2>Your Premier Outboard Motor Dealer</h2>
                <p>For over 25 years, <?php echo SITE_NAME; ?> has been the trusted source for premium outboard motors and marine equipment. We specialize in providing top-quality engines from leading manufacturers including Yamaha, Mercury, Honda, Suzuki, and Tohatsu.</p>
                
                <h3>Our Mission</h3>
                <p>We are committed to helping boating enthusiasts find the perfect outboard motor for their needs. Whether you're a weekend angler, commercial fisherman, or recreational boater, we have the expertise and inventory to get you on the water with confidence.</p>
                
                <h3>Why Choose Us?</h3>
                <div class="grid grid-2" style="margin: 24px 0;">
                    <div style="padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <h4><i class="fas fa-medal" style="color: #0ea5e9;"></i> Expert Knowledge</h4>
                        <p>Our certified technicians have decades of combined experience with marine engines.</p>
                    </div>
                    <div style="padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <h4><i class="fas fa-warehouse" style="color: #0ea5e9;"></i> Large Inventory</h4>
                        <p>We maintain one of the largest selections of outboard motors in the region.</p>
                    </div>
                    <div style="padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <h4><i class="fas fa-tools" style="color: #0ea5e9;"></i> Full Service</h4>
                        <p>From sales to service, we're your one-stop shop for all outboard motor needs.</p>
                    </div>
                    <div style="padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <h4><i class="fas fa-handshake" style="color: #0ea5e9;"></i> Customer First</h4>
                        <p>We build long-term relationships with our customers based on trust and quality service.</p>
                    </div>
                </div>
                
                <h3>Our Services</h3>
                <ul style="line-height: 1.8;">
                    <li><strong>New Outboard Sales</strong> - Latest models from all major manufacturers</li>
                    <li><strong>Used Motor Sales</strong> - Certified pre-owned engines with warranties</li>
                    <li><strong>Professional Installation</strong> - Expert rigging and setup services</li>
                    <li><strong>Maintenance & Repair</strong> - Factory-trained technicians and genuine parts</li>
                    <li><strong>Winterization Services</strong> - Protect your investment during off-season</li>
                    <li><strong>Parts & Accessories</strong> - Complete selection of marine parts and accessories</li>
                </ul>
                
                <h3>Authorized Dealer</h3>
                <p>We are proud to be an authorized dealer for:</p>
                <div style="display: flex; gap: 16px; flex-wrap: wrap; margin: 16px 0;">
                    <span style="padding: 8px 16px; background: #e0f2fe; border-radius: 20px; font-weight: 600;">Yamaha</span>
                    <span style="padding: 8px 16px; background: #e0f2fe; border-radius: 20px; font-weight: 600;">Mercury</span>
                    <span style="padding: 8px 16px; background: #e0f2fe; border-radius: 20px; font-weight: 600;">Honda</span>
                    <span style="padding: 8px 16px; background: #e0f2fe; border-radius: 20px; font-weight: 600;">Suzuki</span>
                    <span style="padding: 8px 16px; background: #e0f2fe; border-radius: 20px; font-weight: 600;">Tohatsu</span>
                </div>
                
                <h3>Visit Our Showroom</h3>
                <p>Come visit our state-of-the-art facility where you can see our complete selection of outboard motors and speak with our knowledgeable staff. We're conveniently located at:</p>
                
                <div style="background: #f1f5f9; padding: 20px; border-radius: 8px; border-left: 4px solid #0ea5e9; margin: 16px 0;">
                    <strong>123 Marina Drive<br>
                    Coastal City, CC 12345</strong><br><br>
                    <strong>Hours:</strong><br>
                    Monday - Friday: 8:00 AM - 6:00 PM<br>
                    Saturday: 9:00 AM - 5:00 PM<br>
                    Sunday: Closed
                </div>
                
                <div style="text-align: center; margin-top: 32px;">
                    <a href="contact.php" class="btn btn-primary">Contact Us Today</a>
                    <a href="products.php" class="btn btn-outline" style="margin-left: 8px;">Browse Our Inventory</a>
                </div>
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
