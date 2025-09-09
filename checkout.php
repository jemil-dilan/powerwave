<?php
require_once 'includes/config.php';
require_once 'includes/crypto_config.php';
require_once 'includes/functions.php';
require_once 'includes/paypal_config.php';

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

// Get crypto prices and amounts
$cryptoPrices = getCryptoPrices();
$btcAmount = calculateCryptoAmount($grandTotal, 'bitcoin');
$usdtAmount = calculateCryptoAmount($grandTotal, 'usdt');

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
    .payment-method { border:1px solid #e2e8f0; border-radius:10px; padding:12px; cursor:pointer; display:flex; align-items:center; gap:10px; margin-bottom: 8px; }
    .payment-method input { margin-right: 8px; }
    .payment-method.active { border-color:#0ea5e9; background:#e0f2fe; }
    .payment-method.disabled { opacity: 0.5; cursor: not-allowed; background: #f8f9fa; }
    .payment-method.disabled input { cursor: not-allowed; }
    .payment-status { font-size: 12px; color: #6b7280; margin-top: 4px; }
    .payment-status.available { color: #059669; }
    .payment-status.coming-soon { color: #f59e0b; }
    .crypto-amount { font-weight: 600; color: #0ea5e9; }
    .crypto-rate { font-size: 11px; color: #6b7280; }
    .summary { background:white; border:1px solid #e2e8f0; border-radius:12px; padding:16px; }
    .checkout-buttons { display: flex; gap: 12px; margin-top: 20px; }
    .hidden { display: none !important; }
    .crypto-payment-info { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-top: 16px; }
    .crypto-address { background: #1f2937; color: #f9fafb; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 14px; word-break: break-all; margin: 8px 0; }
    .qr-placeholder { width: 200px; height: 200px; background: #f3f4f6; border: 2px dashed #d1d5db; display: flex; align-items: center; justify-content: center; margin: 12px auto; border-radius: 8px; color: #6b7280; }
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

    <?php if (isset($_GET['payment_cancelled'])): ?>
    <div class="alert alert-error">
      <i class="fas fa-exclamation-triangle"></i> Payment was cancelled. Please try again or choose a different payment method.
    </div>
    <?php endif; ?>

    <?php if (isset($cryptoPrices['fallback'])): ?>
    <div class="alert alert-info">
      <i class="fas fa-info-circle"></i> Using fallback cryptocurrency prices. Actual rates may vary slightly.
    </div>
    <?php endif; ?>

    <div class="grid" style="grid-template-columns: 2fr 1fr; gap: 24px;">
      <form action="place_order.php" method="POST" id="checkout-form" class="grid" style="gap:16px;">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

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
            <input class="input" name="billing_country" required value="<?php echo sanitizeInput($user['country'] ?? 'United States'); ?>">
          </div>
        </div>

        <div class="summary">
          <h3>Payment Method</h3>
          <div class="payment-methods">
            <!-- PayPal - Available -->
            <label class="payment-method" data-method="paypal">
              <input type="radio" name="payment_method" value="paypal" required>
              <i class="fab fa-paypal" style="color:#0ea5e9; font-size: 20px;"></i>
              <div>
                <div>PayPal</div>
                <div class="payment-status available">Available - Pay securely with PayPal</div>
              </div>
            </label>

            <!-- Bitcoin - Available -->
            <label class="payment-method" data-method="bitcoin">
              <input type="radio" name="payment_method" value="bitcoin">
              <i class="fab fa-bitcoin" style="color:#f7931a; font-size: 20px;"></i>
              <div>
                <div>Bitcoin (BTC)</div>
                <div class="payment-status available">
                  <span class="crypto-amount"><?php echo number_format($btcAmount, 8); ?> BTC</span>
                  <div class="crypto-rate">1 BTC = $<?php echo number_format($cryptoPrices['bitcoin'], 2); ?></div>
                </div>
              </div>
            </label>

            <!-- USDT - Available -->
            <label class="payment-method" data-method="usdt">
              <input type="radio" name="payment_method" value="usdt">
              <i class="fas fa-coins" style="color:#26a17b; font-size: 18px;"></i>
              <div>
                <div>USDT (Tether)</div>
                <div class="payment-status available">
                  <span class="crypto-amount"><?php echo number_format($usdtAmount, 2); ?> USDT</span>
                  <div class="crypto-rate">Network: <?php echo strtoupper(USDT_NETWORK); ?></div>
                </div>
              </div>
            </label>

            <!-- Bank Transfer - Manual Processing -->
            <label class="payment-method" data-method="bank">
              <input type="radio" name="payment_method" value="bank">
              <i class="fas fa-university" style="color:#6b7280; font-size: 18px;"></i>
              <div>
                <div>Bank Transfer</div>
                <div class="payment-status available">Manual processing - Instructions will be sent</div>
              </div>
            </label>

            <!-- Apple Pay - Coming Soon -->
            <label class="payment-method disabled" data-method="applepay">
              <input type="radio" name="payment_method" value="applepay" disabled>
              <i class="fab fa-apple" style="color:#6b7280; font-size: 18px;"></i>
              <div>
                <div>Apple Pay</div>
                <div class="payment-status coming-soon">Coming Soon</div>
              </div>
            </label>

            <!-- Cash App - Coming Soon -->
            <label class="payment-method disabled" data-method="cashapp">
              <input type="radio" name="payment_method" value="cashapp" disabled>
              <i class="fas fa-dollar-sign" style="color:#6b7280; font-size: 18px;"></i>
              <div>
                <div>Cash App</div>
                <div class="payment-status coming-soon">Coming Soon</div>
              </div>
            </label>
          </div>

          <div id="payment-info" class="hidden" style="margin-top: 16px;">
            <!-- PayPal Info -->
            <div id="paypal-info" class="payment-info-content hidden" style="padding: 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
              <p style="margin: 0; color: #475569; font-size: 14px;">
                <i class="fas fa-info-circle"></i> You will be redirected to PayPal to complete your payment securely.
              </p>
            </div>

            <!-- Bitcoin Info -->
            <div id="bitcoin-info" class="payment-info-content crypto-payment-info hidden">
              <h4 style="margin: 0 0 12px; color: #f7931a;"><i class="fab fa-bitcoin"></i> Bitcoin Payment Instructions</h4>
              <p><strong>Amount to send:</strong> <span class="crypto-amount"><?php echo number_format($btcAmount, 8); ?> BTC</span></p>
              <p><strong>Bitcoin Address:</strong></p>
              <div class="crypto-address"><?php echo BITCOIN_WALLET_ADDRESS; ?></div>
              <div class="qr-placeholder">
                <div style="text-align: center;">
                  <i class="fas fa-qrcode" style="font-size: 32px; margin-bottom: 8px;"></i><br>
                  QR Code will be<br>generated here
                </div>
              </div>
              <div style="background: #fef3c7; padding: 12px; border-radius: 6px; margin-top: 12px;">
                <p style="margin: 0; color: #92400e; font-size: 14px;">
                  <i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong> Send exactly <strong><?php echo number_format($btcAmount, 8); ?> BTC</strong> to the address above.
                  Your order will be processed after <?php echo CRYPTO_CONFIRMATION_BLOCKS; ?> network confirmations.
                </p>
              </div>
            </div>

            <!-- USDT Info -->
            <div id="usdt-info" class="payment-info-content crypto-payment-info hidden">
              <h4 style="margin: 0 0 12px; color: #26a17b;"><i class="fas fa-coins"></i> USDT Payment Instructions</h4>
              <p><strong>Amount to send:</strong> <span class="crypto-amount"><?php echo number_format($usdtAmount, 2); ?> USDT</span></p>
              <p><strong>Network:</strong> <?php echo strtoupper(USDT_NETWORK); ?></p>
              <p><strong>USDT Address:</strong></p>
              <div class="crypto-address"><?php echo USDT_WALLET_ADDRESS; ?></div>
              <div class="qr-placeholder">
                <div style="text-align: center;">
                  <i class="fas fa-qrcode" style="font-size: 32px; margin-bottom: 8px;"></i><br>
                  QR Code will be<br>generated here
                </div>
              </div>
              <div style="background: #fef3c7; padding: 12px; border-radius: 6px; margin-top: 12px;">
                <p style="margin: 0; color: #92400e; font-size: 14px;">
                  <i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong>
                  Send exactly <strong><?php echo number_format($usdtAmount, 2); ?> USDT</strong> on the <strong><?php echo strtoupper(USDT_NETWORK); ?></strong> network.
                  Wrong network = lost funds!
                </p>
              </div>
            </div>

            <!-- Bank Info -->
            <div id="bank-info" class="payment-info-content hidden" style="padding: 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
              <p style="margin: 0; color: #475569; font-size: 14px;">
                <i class="fas fa-info-circle"></i> After placing your order, you'll receive bank transfer instructions via email.
              </p>
            </div>
          </div>
        </div>

        <div class="summary">
          <h3>Order Notes</h3>
          <textarea name="notes" class="input" rows="4" placeholder="Notes about your order, e.g. special delivery instructions"></textarea>
        </div>

        <!-- Traditional Checkout Button -->
        <div id="traditional-checkout" class="checkout-buttons">
          <button class="btn btn-primary" type="submit" style="width: 100%;">
            <i class="fas fa-lock"></i> <span id="checkout-btn-text">Place Order</span>
          </button>
        </div>
      </form>
      
      <!-- PayPal Button Container -->
      <div id="paypal-checkout" class="checkout-buttons hidden">
        <div id="paypal-button-container"></div>
        <p style="color: #6b7280; font-size: 12px; text-align: center; margin-top: 8px;">
          Secure payment powered by PayPal
        </p>
      </div>

      <aside class="summary">
        <h3>Order Summary</h3>
        <div style="display:grid; gap:8px;">
          <?php foreach ($cartItems as $item): ?>
            <div style="display:flex; justify-content:space-between; align-items: center;">
              <div style="flex: 1;">
                <div style="font-weight: 600;"><?php echo sanitizeInput($item['name']); ?></div>
                <div style="color: #6b7280; font-size: 14px;">Qty: <?php echo (int)$item['quantity']; ?> × <?php echo formatPrice($item['price']); ?></div>
              </div>
              <div style="font-weight: 600;"><?php echo formatPrice($item['price'] * $item['quantity']); ?></div>
            </div>
          <?php endforeach; ?>
          <hr style="margin: 12px 0; border: none; border-top: 1px solid #e2e8f0;">
          <div style="display:flex; justify-content:space-between;"><span>Subtotal</span><strong><?php echo formatPrice($cartTotal); ?></strong></div>
          <div style="display:flex; justify-content:space-between;"><span>Shipping</span><strong><?php echo formatPrice($shipping); ?></strong></div>
          <div style="display:flex; justify-content:space-between;"><span>Tax (<?php echo (TAX_RATE * 100); ?>%)</span><strong><?php echo formatPrice($tax); ?></strong></div>
          <hr style="margin: 12px 0; border: none; border-top: 1px solid #e2e8f0;">
          <div style="display:flex; justify-content:space-between; font-size:18px; font-weight: 700;"><span>Total</span><strong><?php echo formatPrice($grandTotal); ?></strong></div>

          <!-- Crypto amounts -->
          <div style="margin-top: 12px; padding: 12px; background: #f8fafc; border-radius: 6px; font-size: 14px;">
            <div style="color: #6b7280;">Cryptocurrency equivalents:</div>
            <div style="display: flex; justify-content: space-between; margin-top: 4px;">
              <span><i class="fab fa-bitcoin" style="color: #f7931a;"></i> Bitcoin:</span>
              <span><?php echo number_format($btcAmount, 8); ?> BTC</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
              <span><i class="fas fa-coins" style="color: #26a17b;"></i> USDT:</span>
              <span><?php echo number_format($usdtAmount, 2); ?> USDT</span>
            </div>
            <div style="font-size: 11px; color: #9ca3af; margin-top: 4px;">
              Rates updated: <?php echo date('H:i', $cryptoPrices['last_updated']); ?>
            </div>
          </div>
        </div>

        <div style="margin-top: 16px; padding: 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
          <div style="display: flex; align-items: center; gap: 8px; color: #166534; font-size: 14px;">
            <i class="fas fa-shield-alt"></i>
            <span style="font-weight: 600;">Secure Checkout</span>
          </div>
          <p style="margin: 4px 0 0; color: #166534; font-size: 12px;">All payment methods are secure and encrypted</p>
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
    // Payment method selection handling
    document.addEventListener('change', (e) => {
      if (e.target.name === 'payment_method') {
        const selectedMethod = e.target.value;

        // Update visual states
        document.querySelectorAll('.payment-method').forEach(el => {
          el.classList.remove('active');
          if (el.querySelector('input').checked) {
            el.classList.add('active');
          }
        });

        // Show/hide payment info
        const paymentInfo = document.getElementById('payment-info');
        const allInfoContent = document.querySelectorAll('.payment-info-content');

        allInfoContent.forEach(content => content.classList.add('hidden'));
        
        if (['paypal', 'bitcoin', 'usdt', 'bank'].includes(selectedMethod)) {
          paymentInfo.classList.remove('hidden');
          document.getElementById(selectedMethod + '-info').classList.remove('hidden');
        } else {
          paymentInfo.classList.add('hidden');
        }
        
        // Handle checkout buttons and button text
        const traditionalCheckout = document.getElementById('traditional-checkout');
        const paypalCheckout = document.getElementById('paypal-checkout');
        const checkoutBtnText = document.getElementById('checkout-btn-text');

        if (selectedMethod === 'paypal') {
          // Show PayPal button, hide traditional
          traditionalCheckout.classList.add('hidden');
          paypalCheckout.classList.remove('hidden');
          initializePayPalButton();
        } else if (['bank', 'bitcoin', 'usdt'].includes(selectedMethod)) {
          // Show traditional button for manual payment methods
          paypalCheckout.classList.add('hidden');
          traditionalCheckout.classList.remove('hidden');

          // Update button text based on payment method
          if (selectedMethod === 'bitcoin') {
            checkoutBtnText.innerHTML = '<i class="fab fa-bitcoin"></i> Place Order - Pay with Bitcoin';
          } else if (selectedMethod === 'usdt') {
            checkoutBtnText.innerHTML = '<i class="fas fa-coins"></i> Place Order - Pay with USDT';
          } else if (selectedMethod === 'bank') {
            checkoutBtnText.innerHTML = '<i class="fas fa-university"></i> Place Order - Bank Transfer';
          }
        } else {
          // For disabled methods, hide both buttons
          paypalCheckout.classList.add('hidden');
          traditionalCheckout.classList.add('hidden');
        }
      }
    });
    
    // Prevent selection of disabled payment methods
    document.addEventListener('click', (e) => {
      if (e.target.closest('.payment-method.disabled')) {
        e.preventDefault();
        return false;
      }
    });

    // Copy crypto address to clipboard
    document.addEventListener('click', (e) => {
      if (e.target.closest('.crypto-address')) {
        const address = e.target.textContent;
        navigator.clipboard.writeText(address).then(() => {
          // Show temporary feedback
          const original = e.target.innerHTML;
          e.target.innerHTML = 'Address copied!';
          e.target.style.background = '#059669';
          setTimeout(() => {
            e.target.innerHTML = original;
            e.target.style.background = '#1f2937';
          }, 2000);
        }).catch(() => {
          alert('Please manually copy the address: ' + address);
        });
      }
    });

    // Initialize PayPal button
    function initializePayPalButton() {
      document.getElementById('paypal-button-container').innerHTML = '';
      
      if (typeof paypal === 'undefined') {
        console.error('PayPal SDK not loaded');
        document.getElementById('paypal-button-container').innerHTML =
          '<div style="color: #ef4444; text-align: center; padding: 16px;">PayPal SDK failed to load. Please refresh or use another payment method.</div>';
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
          document.getElementById('paypal-button-container').innerHTML =
            '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Processing payment...</div>';

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
              window.location.href = data.redirect_url;
            } else {
              throw new Error(data.error || 'Failed to capture payment');
            }
          })
          .catch(err => {
            console.error('Error capturing PayPal payment:', err);
            alert('Error processing payment: ' + err.message);
            // Reinitialize PayPal button
            initializePayPalButton();
          });
        },
        
        onError: function(err) {
          console.error('PayPal error:', err);
          alert('PayPal error occurred. Please try another payment method.');
          document.getElementById('paypal-button-container').innerHTML =
            '<div style="text-align: center; color: #ef4444; padding: 16px;">PayPal temporarily unavailable.</div>';
        },
        
        onCancel: function(data) {
          console.log('PayPal payment cancelled', data);
          // User cancelled, just show the button again
          initializePayPalButton();
        },
        
        style: {
          layout: 'vertical',
          color: 'blue',
          shape: 'rect',
          label: 'paypal',
          height: 45
        }
      }).render('#paypal-button-container');
    }
    
    // Initialize on page load - default to bank transfer if available
    document.addEventListener('DOMContentLoaded', function() {
      // Check if there are any enabled payment methods
      const enabledMethods = document.querySelectorAll('.payment-method:not(.disabled) input');
      if (enabledMethods.length > 0) {
        // Default to bank transfer if available, otherwise first available method
        const bankMethod = document.querySelector('input[value="bank"]');
        const defaultMethod = bankMethod && !bankMethod.disabled ? bankMethod : enabledMethods[0];
        defaultMethod.checked = true;
        defaultMethod.dispatchEvent(new Event('change'));
      }
    });
  </script>
</body>
</html>