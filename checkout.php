<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/paypal_config.php';
require_once 'includes/PayPalService.php';

requireLogin();

$user = getCurrentUser();
$userId = $_SESSION['user_id'];
$cartItems = getCartItems($userId);
$cartTotal = getCartTotal($userId);

if (empty($cartItems)) {
    showMessage('Your cart is empty. Please add items before checking out.', 'error');
    redirect('cart.php');
}

$shipping = SHIPPING_RATE;
$tax = round($cartTotal * TAX_RATE, 2);
$grandTotal = $cartTotal + $shipping + $tax;

$pageTitle = 'Checkout';
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
  <style>
    .pmethod { border:1px solid #e2e8f0; border-radius:10px; padding:12px; cursor:pointer; display:flex; align-items:center; gap:10px; }
    .pmethod input { margin-right: 8px; }
    .pmethod.active { border-color:#0ea5e9; background:#e0f2fe; }
    .summary { background:white; border:1px solid #e2e8f0; border-radius:12px; padding:16px; }
  </style>
</head>
<body>
  <header class="header">
    <div class="container">
      <div class="main-header">
        <div class="logo"><a href="index.php"><h1><i class="fas fa-anchor"></i> <?php echo SITE_NAME; ?></h1></a></div>
        <div class="cart-info">
          <a href="cart.php" class="cart-link">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count"><?php echo getCartItemCount($userId); ?></span>
            <span class="cart-total"><?php echo formatPrice($cartTotal); ?></span>
          </a>
        </div>
      </div>
    </div>
  </header>

  <main class="container">
    <h1>Checkout</h1>
    <?php displayMessage(); ?>
    <div class="grid" style="grid-template-columns: 2fr 1fr; gap: 24px;">
      <form action="place_order.php" method="POST" class="grid" style="gap:16px;">
        <div class="summary">
          <h3>Billing Details</h3>
          <div class="grid grid-2">
            <div class="form-group">
              <label>First Name *</label>
              <input class="input" name="billing_first_name" required value="<?php echo sanitizeInput($user['first_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
              <label>Last Name *</label>
              <input class="input" name="billing_last_name" required value="<?php echo sanitizeInput($user['last_name'] ?? ''); ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input type="email" class="input" name="billing_email" required value="<?php echo sanitizeInput($user['email'] ?? ''); ?>">
          </div>
          <div class="form-group">
            <label>Phone</label>
            <input class="input" name="billing_phone" value="<?php echo sanitizeInput($user['phone'] ?? ''); ?>">
          </div>
          <div class="form-group">
            <label>Address *</label>
            <input class="input" name="billing_address" required value="<?php echo sanitizeInput($user['address'] ?? ''); ?>">
          </div>
          <div class="grid grid-3">
            <div class="form-group">
              <label>City *</label>
              <input class="input" name="billing_city" required value="<?php echo sanitizeInput($user['city'] ?? ''); ?>">
            </div>
            <div class="form-group">
              <label>State/Province *</label>
              <input class="input" name="billing_state" required value="<?php echo sanitizeInput($user['state'] ?? ''); ?>">
            </div>
            <div class="form-group">
              <label>Postal Code *</label>
              <input class="input" name="billing_zip" required value="<?php echo sanitizeInput($user['zip_code'] ?? ''); ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Country *</label>
            <input class="input" name="billing_country" required value="<?php echo sanitizeInput($user['country'] ?? ''); ?>">
          </div>
        </div>

        <div class="summary">
          <h3>Payment Method</h3>
          <div class="grid grid-2">
            <label class="pmethod"><input type="radio" name="payment_method" value="paypal" required> <i class="fab fa-paypal" style="color:#0ea5e9"></i> PayPal</label>
            <label class="pmethod"><input type="radio" name="payment_method" value="bank"> <i class="fas fa-university" style="color:#0ea5e9"></i> Bank Transfer</label>
            <label class="pmethod"><input type="radio" name="payment_method" value="applepay"> <i class="fab fa-apple" style="color:#0ea5e9"></i> Apple Pay</label>
            <label class="pmethod"><input type="radio" name="payment_method" value="cashapp"> <i class="fas fa-dollar-sign" style="color:#0ea5e9"></i> Cash App</label>
          </div>
          <p style="color:#64748b; font-size:14px; margin-top:8px;">You can replace the placeholder credentials in includes/config.php with your real merchant keys later.</p>
        </div>

        <div class="summary">
          <h3>Order Notes</h3>
          <textarea name="notes" class="input" rows="4" placeholder="Notes about your order, e.g. special delivery instructions"></textarea>
        </div>

        <div id="traditional-checkout-form">
          <button class="btn btn-primary" type="submit"><i class="fas fa-lock"></i> Place Order</button>
        </div>
      </form>
      
      <!-- PayPal Button Container -->
      <div id="paypal-button-container" style="display: none; margin-top: 16px;"></div>

      <aside class="summary">
        <h3>Order Summary</h3>
        <div style="display:grid; gap:8px;">
          <?php foreach ($cartItems as $item): ?>
            <div style="display:flex; justify-content:space-between;">
              <div><?php echo sanitizeInput($item['name']); ?> × <?php echo (int)$item['quantity']; ?></div>
              <div><?php echo formatPrice($item['price'] * $item['quantity']); ?></div>
            </div>
          <?php endforeach; ?>
          <hr>
          <div style="display:flex; justify-content:space-between;"><span>Subtotal</span><strong><?php echo formatPrice($cartTotal); ?></strong></div>
          <div style="display:flex; justify-content:space-between;"><span>Shipping</span><strong><?php echo formatPrice($shipping); ?></strong></div>
          <div style="display:flex; justify-content:space-between;"><span>Tax</span><strong><?php echo formatPrice($tax); ?></strong></div>
          <div style="display:flex; justify-content:space-between; font-size:18px; margin-top:8px;"><span>Total</span><strong><?php echo formatPrice($grandTotal); ?></strong></div>
        </div>
      </aside>
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

  <!-- PayPal JavaScript SDK -->
  <script src="<?php echo getPayPalSDKUrl(PAYPAL_CURRENCY, 'capture'); ?>"></script>
  
  <script>
    // Toggle visual active state for selected payment method
    document.addEventListener('change', (e) => {
      if (e.target.name === 'payment_method') {
        document.querySelectorAll('.pmethod').forEach(el => el.classList.remove('active'));
        e.target.closest('.pmethod').classList.add('active');
        
        // Show/hide PayPal button based on selection
        const paypalContainer = document.getElementById('paypal-button-container');
        const traditionalForm = document.getElementById('traditional-checkout-form');
        
        if (e.target.value === 'paypal') {
          paypalContainer.style.display = 'block';
          traditionalForm.style.display = 'none';
          initializePayPalButton();
        } else {
          paypalContainer.style.display = 'none';
          traditionalForm.style.display = 'block';
        }
      }
    });
    
    // Initialize PayPal button
    function initializePayPalButton() {
      // Clear existing button
      document.getElementById('paypal-button-container').innerHTML = '';
      
      if (typeof paypal === 'undefined') {
        console.error('PayPal SDK not loaded');
        return;
      }
      
      paypal.Buttons({
        createOrder: function(data, actions) {
          return fetch('api/paypal_create_order.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              amount: <?php echo $grandTotal; ?>,
              currency: '<?php echo PAYPAL_CURRENCY; ?>'
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              return data.order_id;
            } else {
              throw new Error(data.error || 'Failed to create order');
            }
          })
          .catch(err => {
            console.error('Error creating PayPal order:', err);
            alert('Error creating PayPal order: ' + err.message);
          });
        },
        
        onApprove: function(data, actions) {
          return fetch('api/paypal_capture_order.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              orderID: data.orderID
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Redirect to success page
              window.location.href = data.redirect_url;
            } else {
              throw new Error(data.error || 'Failed to capture payment');
            }
          })
          .catch(err => {
            console.error('Error capturing PayPal payment:', err);
            alert('Error processing payment: ' + err.message);
          });
        },
        
        onError: function(err) {
          console.error('PayPal error:', err);
          alert('PayPal error occurred. Please try again or use a different payment method.');
        },
        
        onCancel: function(data) {
          console.log('PayPal payment cancelled', data);
          alert('Payment was cancelled.');
        },
        
        style: {
          layout: 'vertical',
          color: 'blue',
          shape: 'rect',
          label: 'paypal'
        }
      }).render('#paypal-button-container');
    }
    
    // Auto-select PayPal if it's the only checked option
    document.addEventListener('DOMContentLoaded', function() {
      const paypalRadio = document.querySelector('input[value="paypal"]');
      if (paypalRadio && paypalRadio.checked) {
        paypalRadio.dispatchEvent(new Event('change'));
      }
    });
  </script>
</body>
</html>
