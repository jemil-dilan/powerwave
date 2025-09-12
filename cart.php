<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$userId = isLoggedIn() ? $_SESSION['user_id'] : null;
$cartItems = getCartItems($userId);
$cartTotal = getCartTotal($userId);
$cartCount = getCartItemCount($userId);

// Handle cart updates
if ($_POST) {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        showMessage('Invalid security token. Please try again.', 'error');
        redirect('cart.php');
    }

    $db = Database::getInstance();
    
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $cartId => $quantity) {
            $cartId = (int)$cartId;
            $quantity = max(0, (int)$quantity);
            
            if ($quantity === 0) {
                // Remove item
                $db->delete('cart', 'id = ?', [$cartId]);
            } else {
                // Update quantity
                $db->update('cart', ['quantity' => $quantity], 'id = ?', [$cartId]);
            }
        }
        
        showMessage('Cart updated successfully', 'success');
        redirect('cart.php');
    }
}

$pageTitle = 'Shopping Cart';
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
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
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
<span class="cart-count"><?php echo $cartCount; ?></span>
                        <span class="cart-total"><?php echo formatPriceSafe($cartTotal); ?></span>
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
        <?php displayMessage(); ?>
        
        <h2 class="section-title">Shopping Cart</h2>
        
        <?php if (empty($cartItems)): ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 12px; border: 1px solid #e2e8f0;">
                <i class="fas fa-shopping-cart" style="font-size: 48px; color: #cbd5e1; margin-bottom: 16px;"></i>
                <h3>Your cart is empty</h3>
                <p>Add some outboard motors to your cart to get started.</p>
                <a href="products.php" class="btn btn-primary">Shop Now</a>
            </div>
        <?php else: ?>
            <form method="POST" action="cart.php">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="cart-table" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
                    <div class="cart-header" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 16px; padding: 16px; background: #f8fafc; font-weight: 600; border-bottom: 1px solid #e2e8f0;">
                        <div>Product</div>
                        <div>Price</div>
                        <div>Quantity</div>
                        <div>Total</div>
                        <div></div>
                    </div>
                    
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 16px; padding: 16px; align-items: center; border-bottom: 1px solid #f1f5f9;">
                            <div style="display: flex; gap: 12px; align-items: center;">
                                <img src="<?php echo getProductImageUrl($item['main_image']); ?>" 
                                     alt="<?php echo sanitizeInput($item['name']); ?>" 
                                     style="width: 64px; height: 64px; object-fit: cover; border-radius: 8px;">
                                <div>
                                    <h4 style="margin: 0; font-size: 16px;">
                                        <a href="product.php?id=<?php echo $item['product_id']; ?>">
                                            <?php echo sanitizeInput($item['name']); ?>
                                        </a>
                                    </h4>
                                    <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">
                                        <?php echo sanitizeInput($item['brand_name']); ?>
                                    </p>
                                </div>
                            </div>
                            <div><?php echo formatPriceSafe($item['price']); ?></div>
                            <div>
                                <input type="number" name="quantities[<?php echo $item['id']; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="0" max="99" 
                                       style="width: 80px; padding: 8px; border: 1px solid #cbd5e1; border-radius: 8px;">
                            </div>
                            <div><?php echo formatPriceSafe($item['price'] * $item['quantity']); ?></div>
                            <div>
                                <button type="button" class="remove-item" data-cart-id="<?php echo $item['id']; ?>" 
                                        style="color: #ef4444; background: none; border: none; cursor: pointer; padding: 8px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin: 16px 0;">
                    <button type="submit" name="update_cart" class="btn btn-outline">Update Cart</button>
                    <div style="text-align: right;">
                        <div style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">
                            Total: <?php echo formatPriceSafe($cartTotal); ?>
                        </div>
                        <div style="color: #64748b; font-size: 14px;">
                            + <?php echo formatPriceSafe(SHIPPING_RATE); ?> shipping
                        </div>
                    </div>
                </div>
                
                <div style="text-align: center; margin: 24px 0;">
                    <?php if (!isLoggedIn()): ?>
                        <p style="margin-bottom: 16px; color: #64748b;">Please log in to proceed to checkout</p>
                        <a href="login.php?redirect=cart.php" class="btn btn-primary">Login to Checkout</a>
                    <?php else: ?>
                        <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                    <?php endif; ?>
                    <a href="products.php" class="btn btn-outline" style="margin-left: 8px;">Continue Shopping</a>
                </div>
            </form>
        <?php endif; ?>
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
