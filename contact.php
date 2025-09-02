<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Handle form submission
if ($_POST && isset($_POST['submit_contact'])) {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    
    $errors = [];
    
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($subject)) $errors[] = 'Subject is required';
    if (empty($message)) $errors[] = 'Message is required';
    
    if (!$errors) {
        try {
            $db = Database::getInstance();
            $db->insert('contact_messages', [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message,
                'status' => 'new'
            ]);
            
            // Send email notification (basic implementation)
            $emailSubject = "New Contact Form Submission: $subject";
            $emailMessage = "
                <h3>New Contact Form Submission</h3>
                <p><strong>Name:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Phone:</strong> $phone</p>
                <p><strong>Subject:</strong> $subject</p>
                <p><strong>Message:</strong></p>
                <p>$message</p>
            ";
            
            sendEmail(ADMIN_EMAIL, $emailSubject, $emailMessage);
            
            showMessage('Thank you for your message! We will get back to you soon.', 'success');
            
            // Clear form data
            $_POST = [];
            
        } catch (Exception $e) {
            showMessage('There was an error sending your message. Please try again.', 'error');
        }
    } else {
        showMessage(implode('<br>', $errors), 'error');
    }
}

$pageTitle = 'Contact Us';
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
                        <span class="cart-total"><?php echo formatPrice(getCartTotal(isLoggedIn() ? $_SESSION['user_id'] : null)); ?></span>
                    </a>
                </div>
            </div>
            <nav class="navigation">
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php" class="active">Contact</a></li>
                </ul>
                <div class="mobile-menu-toggle"><i class="fas fa-bars"></i></div>
            </nav>
        </div>
    </header>

    <main class="container">
        <h1>Contact Us</h1>
        
        <div class="grid grid-2" style="gap: 32px; margin: 24px 0;">
            <!-- Contact Form -->
            <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 32px;">
                <h2>Send Us a Message</h2>
                <?php displayMessage(); ?>
                
                <form method="POST" action="contact.php">
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="name" class="input" required 
                                   value="<?php echo isset($_POST['name']) ? sanitizeInput($_POST['name']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" class="input" required 
                                   value="<?php echo isset($_POST['email']) ? sanitizeInput($_POST['email']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" class="input" 
                                   value="<?php echo isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Subject *</label>
                            <select name="subject" class="input" required>
                                <option value="">Select a subject</option>
                                <option value="General Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="Product Question" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Product Question') ? 'selected' : ''; ?>>Product Question</option>
                                <option value="Service Request" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Service Request') ? 'selected' : ''; ?>>Service Request</option>
                                <option value="Parts Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Parts Inquiry') ? 'selected' : ''; ?>>Parts Inquiry</option>
                                <option value="Warranty Claim" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Warranty Claim') ? 'selected' : ''; ?>>Warranty Claim</option>
                                <option value="Other" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Message *</label>
                        <textarea name="message" class="input" rows="6" required style="resize: vertical;"><?php echo isset($_POST['message']) ? sanitizeInput($_POST['message']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" name="submit_contact" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
            
            <!-- Contact Information -->
            <div>
                <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 32px; margin-bottom: 24px;">
                    <h2>Get in Touch</h2>
                    <p>We're here to help with all your outboard motor needs. Contact us today!</p>
                    
                    <div style="margin: 24px 0;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                            <i class="fas fa-map-marker-alt" style="color: #0ea5e9; font-size: 18px; width: 20px;"></i>
                            <div>
                                <strong>Address</strong><br>
                                123 Marina Drive<br>
                                Coastal City, CC 12345
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                            <i class="fas fa-phone" style="color: #0ea5e9; font-size: 18px; width: 20px;"></i>
                            <div>
                                <strong>Phone</strong><br>
                                <a href="tel:+15551234567" style="color: #0ea5e9;">(555) 123-4567</a>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                            <i class="fas fa-envelope" style="color: #0ea5e9; font-size: 18px; width: 20px;"></i>
                            <div>
                                <strong>Email</strong><br>
                                <a href="mailto:<?php echo SITE_EMAIL; ?>" style="color: #0ea5e9;"><?php echo SITE_EMAIL; ?></a>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                            <i class="fas fa-clock" style="color: #0ea5e9; font-size: 18px; width: 20px;"></i>
                            <div>
                                <strong>Hours</strong><br>
                                Mon-Fri: 8:00 AM - 6:00 PM<br>
                                Saturday: 9:00 AM - 5:00 PM<br>
                                Sunday: Closed
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 32px;">
                    <h3>Emergency Service</h3>
                    <p>Need emergency marine service? Call our 24/7 emergency line:</p>
                    <div style="text-align: center; margin: 16px 0;">
                        <a href="tel:+15551234999" style="font-size: 20px; font-weight: bold; color: #ef4444;">
                            <i class="fas fa-phone"></i> (555) 123-4999
                        </a>
                    </div>
                    <p style="font-size: 14px; color: #64748b; text-align: center;">
                        Emergency service available for existing customers only. Additional charges may apply.
                    </p>
                </div>
                
                <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 32px; margin-top: 24px;">
                    <h3>Follow Us</h3>
                    <div style="display: flex; gap: 16px; justify-content: center;">
                        <a href="#" style="color: #0ea5e9; font-size: 24px;"><i class="fab fa-facebook"></i></a>
                        <a href="#" style="color: #0ea5e9; font-size: 24px;"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="color: #0ea5e9; font-size: 24px;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="color: #0ea5e9; font-size: 24px;"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Map Section (Placeholder) -->
        <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 32px; margin: 24px 0;">
            <h2>Find Us</h2>
            <div style="background: #f1f5f9; height: 300px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #64748b;">
                <div style="text-align: center;">
                    <i class="fas fa-map" style="font-size: 48px; margin-bottom: 16px;"></i>
                    <p>Interactive map would be embedded here<br>
                    <small>Integration with Google Maps, Mapbox, or similar service</small></p>
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
