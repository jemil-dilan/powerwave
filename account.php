<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

$user = getCurrentUser();
$userId = $_SESSION['user_id'];

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    $firstName = sanitizeInput($_POST['first_name'] ?? '');
    $lastName = sanitizeInput($_POST['last_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $state = sanitizeInput($_POST['state'] ?? '');
    $zipCode = sanitizeInput($_POST['zip_code'] ?? '');
    $country = sanitizeInput($_POST['country'] ?? '');
    
    $errors = [];
    
    if (empty($firstName)) $errors[] = 'First name is required';
    if (empty($lastName)) $errors[] = 'Last name is required';
    
    if (!$errors) {
        try {
            $db = Database::getInstance();
            $db->update('users', [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'zip_code' => $zipCode,
                'country' => $country
            ], 'id = ?', [$userId]);
            
            showMessage('Profile updated successfully!', 'success');
            $user = getCurrentUser(); // Refresh user data
        } catch (Exception $e) {
            showMessage('Failed to update profile. Please try again.', 'error');
        }
    } else {
        showMessage(implode('<br>', $errors), 'error');
    }
}

// Handle password change
if ($_POST && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    if (empty($currentPassword)) $errors[] = 'Current password is required';
    if (empty($newPassword)) $errors[] = 'New password is required';
    if (strlen($newPassword) < 6) $errors[] = 'New password must be at least 6 characters';
    if ($newPassword !== $confirmPassword) $errors[] = 'New passwords do not match';
    
    if (!$errors) {
        if (verifyPassword($currentPassword, $user['password'])) {
            try {
                $db = Database::getInstance();
                $db->update('users', [
                    'password' => hashPassword($newPassword)
                ], 'id = ?', [$userId]);
                
                showMessage('Password changed successfully!', 'success');
            } catch (Exception $e) {
                showMessage('Failed to change password. Please try again.', 'error');
            }
        } else {
            $errors[] = 'Current password is incorrect';
        }
    }
    
    if ($errors) {
        showMessage(implode('<br>', $errors), 'error');
    }
}

// Get user orders
$db = Database::getInstance();
$orders = $db->fetchAll(
    "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
    [$userId]
);

$pageTitle = 'My Account';
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
                        <span class="cart-count"><?php echo getCartItemCount($userId); ?></span>
                        <span class="cart-total"><?php echo formatPrice(getCartTotal($userId)); ?></span>
                    </a>
                </div>
            </div>
            <nav class="navigation">
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
                <div class="mobile-menu-toggle"><i class="fas fa-bars"></i></div>
            </nav>
        </div>
    </header>

    <main class="container">
        <h1>My Account</h1>
        <?php displayMessage(); ?>
        
        <div class="grid" style="grid-template-columns: 250px 1fr; gap: 24px;">
            <!-- Account Menu -->
            <nav style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px;">
                <h3>Account Menu</h3>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li><a href="#profile" class="tab-link active" data-tab="profile" style="display: block; padding: 8px 0; color: #0ea5e9; text-decoration: none;"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="#orders" class="tab-link" data-tab="orders" style="display: block; padding: 8px 0; color: #64748b; text-decoration: none;"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                    <li><a href="#password" class="tab-link" data-tab="password" style="display: block; padding: 8px 0; color: #64748b; text-decoration: none;"><i class="fas fa-key"></i> Change Password</a></li>
                    <li><a href="logout.php" style="display: block; padding: 8px 0; color: #ef4444; text-decoration: none;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>

            <!-- Account Content -->
            <div>
                <!-- Profile Tab -->
                <div id="profile" class="tab-content active" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 32px;">
                    <h2>Profile Information</h2>
                    <form method="POST" action="account.php">
                        <div class="grid grid-2">
                            <div class="form-group">
                                <label>First Name *</label>
                                <input type="text" name="first_name" class="input" required 
                                       value="<?php echo sanitizeInput($user['first_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Last Name *</label>
                                <input type="text" name="last_name" class="input" required 
                                       value="<?php echo sanitizeInput($user['last_name'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" class="input" value="<?php echo sanitizeInput($user['email'] ?? ''); ?>" disabled>
                            <small style="color: #64748b;">Email cannot be changed. Contact support if needed.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" class="input" 
                                   value="<?php echo sanitizeInput($user['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" class="input" 
                                   value="<?php echo sanitizeInput($user['address'] ?? ''); ?>">
                        </div>
                        
                        <div class="grid grid-3">
                            <div class="form-group">
                                <label>City</label>
                                <input type="text" name="city" class="input" 
                                       value="<?php echo sanitizeInput($user['city'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>State/Province</label>
                                <input type="text" name="state" class="input" 
                                       value="<?php echo sanitizeInput($user['state'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Postal Code</label>
                                <input type="text" name="zip_code" class="input" 
                                       value="<?php echo sanitizeInput($user['zip_code'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Country</label>
                            <input type="text" name="country" class="input" 
                                   value="<?php echo sanitizeInput($user['country'] ?? ''); ?>">
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>

                <!-- Orders Tab -->
                <div id="orders" class="tab-content" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 32px; display: none;">
                    <h2>Order History</h2>
                    <?php if (empty($orders)): ?>
                        <p>You haven't placed any orders yet.</p>
                        <a href="products.php" class="btn btn-primary">Start Shopping</a>
                    <?php else: ?>
                        <div class="orders-list">
                            <?php foreach ($orders as $order): ?>
                                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <h4 style="margin: 0;">Order #<?php echo sanitizeInput($order['order_number']); ?></h4>
                                        <span style="padding: 4px 8px; background: 
                                            <?php 
                                            switch($order['status']) {
                                                case 'pending': echo '#fef3c7; color: #d97706'; break;
                                                case 'processing': echo '#dbeafe; color: #1d4ed8'; break;
                                                case 'shipped': echo '#e0e7ff; color: #6366f1'; break;
                                                case 'delivered': echo '#dcfce7; color: #059669'; break;
                                                default: echo '#f3f4f6; color: #374151';
                                            }
                                            ?>; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                    <p style="margin: 4px 0; color: #64748b;">
                                        <strong>Date:</strong> <?php echo date('M j, Y', strtotime($order['created_at'])); ?> | 
                                        <strong>Total:</strong> <?php echo formatPrice($order['total_amount']); ?> | 
                                        <strong>Payment:</strong> <?php echo ucfirst($order['payment_method']); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Password Tab -->
                <div id="password" class="tab-content" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 32px; display: none;">
                    <h2>Change Password</h2>
                    <form method="POST" action="account.php" style="max-width: 400px;">
                        <div class="form-group">
                            <label>Current Password *</label>
                            <input type="password" name="current_password" class="input" required>
                        </div>
                        
                        <div class="form-group">
                            <label>New Password *</label>
                            <input type="password" name="new_password" class="input" required minlength="6">
                            <small style="color: #64748b;">Minimum 6 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Confirm New Password *</label>
                            <input type="password" name="confirm_password" class="input" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
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
    <script>
        // Tab switching functionality
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('tab-link')) {
                e.preventDefault();
                
                // Remove active class from all tabs and links
                document.querySelectorAll('.tab-link').forEach(link => {
                    link.classList.remove('active');
                    link.style.color = '#64748b';
                });
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                    content.style.display = 'none';
                });
                
                // Add active class to clicked tab
                e.target.classList.add('active');
                e.target.style.color = '#0ea5e9';
                
                const tabId = e.target.getAttribute('data-tab');
                const tabContent = document.getElementById(tabId);
                if (tabContent) {
                    tabContent.classList.add('active');
                    tabContent.style.display = 'block';
                }
            }
        });
    </script>
</body>
</html>
