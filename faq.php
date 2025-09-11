<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$categories = getAllCategories();
$brands = getAllBrands();

$pageTitle = 'Frequently Asked Questions';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Find answers to common questions about outboard motors, installation, maintenance, and purchasing. Get expert advice from our marine specialists.">
    <meta name="keywords" content="outboard motor FAQ, marine engine questions, boat motor help, outboard installation, maintenance questions">
    
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
        
        <!-- Page Header -->
        <section class="page-header">
            <div class="container">
                <div class="header-content">
                    <h1>Frequently Asked Questions</h1>
                    <p>Find answers to common questions about outboard motors, installation, maintenance, and our services. Can't find what you're looking for? Contact our experts.</p>
                </div>
            </div>
        </section>
        
        <!-- FAQ Content -->
        <section class="policy-section">
            <div class="container">
                <div class="policy-content">
                    
                    <!-- FAQ Categories -->
                    <div class="faq-categories">
                        <div class="faq-category active" data-category="all">All Questions</div>
                        <div class="faq-category" data-category="purchasing">Purchasing</div>
                        <div class="faq-category" data-category="installation">Installation</div>
                        <div class="faq-category" data-category="maintenance">Maintenance</div>
                        <div class="faq-category" data-category="warranty">Warranty</div>
                        <div class="faq-category" data-category="technical">Technical</div>
                    </div>
                    
                    <!-- FAQ Items -->
                    <div class="faq-list">
                        
                        <!-- Purchasing Questions -->
                        <div class="faq-item" data-category="purchasing">
                            <div class="faq-question">
                                <h3>How do I choose the right outboard motor for my boat?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Choosing the right outboard motor depends on several factors:</p>
                                <ul>
                                    <li><strong>Boat size and weight:</strong> Larger boats need more horsepower</li>
                                    <li><strong>Intended use:</strong> Fishing, cruising, or watersports have different requirements</li>
                                    <li><strong>Transom height:</strong> Measure your boat's transom to determine shaft length</li>
                                    <li><strong>Maximum horsepower rating:</strong> Never exceed your boat's maximum HP rating</li>
                                </ul>
                                <p>Our experts can help you find the perfect match. Contact us for a free consultation!</p>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-category="purchasing">
                            <div class="faq-question">
                                <h3>Do you offer financing options?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Yes! We offer several financing options to make your purchase more affordable:</p>
                                <ul>
                                    <li>0% APR financing for qualified buyers</li>
                                    <li>Extended payment terms up to 84 months</li>
                                    <li>Trade-in credit toward your new motor</li>
                                    <li>Seasonal payment programs</li>
                                </ul>
                                <p>Contact our finance department for a quick pre-approval.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-category="purchasing">
                            <div class="faq-question">
                                <h3>What's included with my outboard motor purchase?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Every new outboard motor includes:</p>
                                <ul>
                                    <li>Standard propeller</li>
                                    <li>Owner's manual and documentation</li>
                                    <li>Full manufacturer warranty</li>
                                    <li>Basic rigging hardware</li>
                                    <li>Pre-delivery inspection</li>
                                </ul>
                                <p>Additional accessories like controls, gauges, and premium propellers are available separately.</p>
                            </div>
                        </div>
                        
                        <!-- Installation Questions -->
                        <div class="faq-item" data-category="installation">
                            <div class="faq-question">
                                <h3>Do you provide installation services?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Yes! We provide professional installation services with certified marine technicians:</p>
                                <ul>
                                    <li>Complete rigging and installation</li>
                                    <li>Control and gauge installation</li>
                                    <li>Sea trial and testing</li>
                                    <li>Owner orientation and training</li>
                                </ul>
                                <p>Professional installation is recommended to maintain warranty coverage and ensure optimal performance.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-category="installation">
                            <div class="faq-question">
                                <h3>How long does installation take?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Installation time varies depending on the complexity:</p>
                                <ul>
                                    <li><strong>Simple repower:</strong> 4-6 hours</li>
                                    <li><strong>New installation with rigging:</strong> 1-2 days</li>
                                    <li><strong>Multi-engine installations:</strong> 2-3 days</li>
                                    <li><strong>Custom rigging projects:</strong> 3-5 days</li>
                                </ul>
                                <p>We'll provide an accurate timeline when you schedule your installation.</p>
                            </div>
                        </div>
                        
                        <!-- Maintenance Questions -->
                        <div class="faq-item" data-category="maintenance">
                            <div class="faq-question">
                                <h3>How often should I service my outboard motor?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Regular maintenance is crucial for performance and longevity:</p>
                                <ul>
                                    <li><strong>Every 100 hours or annually:</strong> Oil change, filter replacement, spark plugs</li>
                                    <li><strong>Every 300 hours:</strong> Water pump impeller, thermostat inspection</li>
                                    <li><strong>Seasonally:</strong> Winterization and spring commissioning</li>
                                    <li><strong>After saltwater use:</strong> Fresh water flush</li>
                                </ul>
                                <p>We offer maintenance packages to keep your motor in peak condition.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-category="maintenance">
                            <div class="faq-question">
                                <h3>What type of oil should I use?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Always use the manufacturer's recommended oil:</p>
                                <ul>
                                    <li><strong>4-stroke motors:</strong> Marine-grade 10W-30 or 10W-40 oil</li>
                                    <li><strong>2-stroke motors:</strong> TCW-3 certified 2-stroke oil</li>
                                    <li><strong>Lower unit:</strong> Marine gear oil (SAE 80W-90)</li>
                                </ul>
                                <p>Using the correct oil is essential for warranty coverage and optimal performance.</p>
                            </div>
                        </div>
                        
                        <!-- Technical Questions -->
                        <div class="faq-item" data-category="technical">
                            <div class="faq-question">
                                <h3>What's the difference between 2-stroke and 4-stroke motors?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Both engine types have their advantages:</p>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 16px 0;">
                                    <div>
                                        <h4>2-Stroke Motors</h4>
                                        <ul>
                                            <li>Lighter weight</li>
                                            <li>Higher power-to-weight ratio</li>
                                            <li>Simpler design</li>
                                            <li>Lower initial cost</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h4>4-Stroke Motors</h4>
                                        <ul>
                                            <li>Better fuel efficiency</li>
                                            <li>Lower emissions</li>
                                            <li>Quieter operation</li>
                                            <li>Longer engine life</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-category="technical">
                            <div class="faq-question">
                                <h3>How do I determine the correct propeller for my boat?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Propeller selection is crucial for optimal performance:</p>
                                <ul>
                                    <li><strong>Diameter:</strong> Larger props provide more thrust but slower acceleration</li>
                                    <li><strong>Pitch:</strong> Higher pitch increases top speed but reduces acceleration</li>
                                    <li><strong>Material:</strong> Aluminum for general use, stainless steel for performance</li>
                                    <li><strong>Blade count:</strong> 3-blade for speed, 4-blade for grip and smooth operation</li>
                                </ul>
                                <p>We offer free propeller consultations to find your optimal setup.</p>
                            </div>
                        </div>
                        
                        <!-- Warranty Questions -->
                        <div class="faq-item" data-category="warranty">
                            <div class="faq-question">
                                <h3>What does the warranty cover?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Our warranty covers manufacturing defects and material failures:</p>
                                <ul>
                                    <li>Engine block and internal components</li>
                                    <li>Electrical system failures</li>
                                    <li>Fuel and cooling system components</li>
                                    <li>Factory-installed accessories</li>
                                </ul>
                                <p>Normal wear items like spark plugs, filters, and impellers are not covered. <a href="warranty.php">View full warranty details</a>.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-category="warranty">
                            <div class="faq-question">
                                <h3>Do I need to register my motor for warranty coverage?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Yes, warranty registration is required within 30 days of purchase to activate coverage. We can help you register your motor, or you can do it online at the manufacturer's website.</p>
                                <p>Keep your purchase receipt and product documentation safe - you'll need them for warranty claims.</p>
                            </div>
                        </div>
                        
                        <!-- General Questions -->
                        <div class="faq-item" data-category="all">
                            <div class="faq-question">
                                <h3>Do you service motors purchased elsewhere?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Absolutely! Our certified technicians service all major outboard motor brands regardless of where you purchased them. We use genuine OEM parts and follow manufacturer specifications for all repairs and maintenance.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-category="all">
                            <div class="faq-question">
                                <h3>Do you buy used outboard motors?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Yes, we purchase used outboard motors in good condition. We offer:</p>
                                <ul>
                                    <li>Free appraisals and evaluations</li>
                                    <li>Competitive cash offers</li>
                                    <li>Trade-in credit toward new purchases</li>
                                    <li>Consignment services for premium motors</li>
                                </ul>
                                <p>Bring your motor by for a free evaluation!</p>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-category="technical">
                            <div class="faq-question">
                                <h3>How do I winterize my outboard motor?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Proper winterization protects your motor during storage:</p>
                                <ol>
                                    <li>Run the motor with fuel stabilizer added</li>
                                    <li>Change the engine oil and filter</li>
                                    <li>Fog the cylinders with fogging oil</li>
                                    <li>Drain the cooling system or add antifreeze</li>
                                    <li>Grease all fittings</li>
                                    <li>Store in a dry, covered area</li>
                                </ol>
                                <p>We offer professional winterization services if you prefer to have it done by experts.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-category="purchasing">
                            <div class="faq-question">
                                <h3>What's your return policy?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>We offer a 30-day return policy on most items in original condition. Items must be unused, in original packaging, and returned with all documentation.</p>
                                <p>Installation voids the return policy, but warranty coverage still applies. <a href="returns.php">View our complete returns policy</a>.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-category="all">
                            <div class="faq-question">
                                <h3>Do you ship nationwide?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Yes, we ship outboard motors and accessories nationwide. Shipping options include:</p>
                                <ul>
                                    <li>Free shipping on orders over $1,000</li>
                                    <li>White glove delivery for motors</li>
                                    <li>Freight shipping for large items</li>
                                    <li>Express shipping available</li>
                                </ul>
                                <p>Delivery times vary by location and product size.</p>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- Contact for More Help -->
                    <div class="contact-section">
                        <h2>Still Have Questions?</h2>
                        <p>Our knowledgeable staff is ready to help with any questions about outboard motors, installation, or service.</p>
                        
                        <div class="contact-options">
                            <div class="contact-option">
                                <i class="fas fa-phone"></i>
                                <div>
                                    <h4>Call Our Experts</h4>
                                    <p>(555) 123-4567<br>Mon-Fri: 8AM-6PM EST</p>
                                </div>
                            </div>
                            <div class="contact-option">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <h4>Email Questions</h4>
                                    <p><?php echo SITE_EMAIL; ?><br>Response within 24 hours</p>
                                </div>
                            </div>
                            <div class="contact-option">
                                <i class="fas fa-store"></i>
                                <div>
                                    <h4>Visit Our Showroom</h4>
                                    <p>123 Marina Drive<br>See motors in person</p>
                                </div>
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin-top: 32px;">
                            <a href="contact.php" class="btn btn-primary">Contact Our Experts</a>
                            <a href="products.php" class="btn btn-outline" style="margin-left: 8px;">Browse Products</a>
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
    <script>
        // FAQ Functionality
        document.addEventListener('DOMContentLoaded', function() {
            // FAQ Toggle
            const faqQuestions = document.querySelectorAll('.faq-question');
            faqQuestions.forEach(question => {
                question.addEventListener('click', function() {
                    const faqItem = this.parentElement;
                    faqItem.classList.toggle('active');
                });
            });
            
            // FAQ Category Filter
            const categoryButtons = document.querySelectorAll('.faq-category');
            const faqItems = document.querySelectorAll('.faq-item');
            
            categoryButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const category = this.getAttribute('data-category');
                    
                    // Update active category
                    categoryButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Filter FAQ items
                    faqItems.forEach(item => {
                        if (category === 'all' || item.getAttribute('data-category') === category) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
