# PayPal Integration Documentation

## Overview
This outboard motors e-commerce website now includes a complete PayPal integration using PayPal's modern JavaScript SDK v2 and Orders API v2.

## Features Implemented

### ✅ PayPal JavaScript SDK Integration
- Modern PayPal buttons with real-time payment processing
- No redirects required - payments happen in modal/popup
- Better user experience with instant feedback

### ✅ PayPal Orders API v2
- Modern REST API implementation
- Better error handling and response structure
- Support for advanced features like partial captures

### ✅ Comprehensive Database Schema
- Enhanced orders table with PayPal-specific fields
- Dedicated PayPal transactions table for detailed tracking
- Proper indexing for performance

### ✅ Webhook Support
- PayPal webhook handler for production notifications
- Automatic order status updates
- Support for refunds and payment status changes

### ✅ Security Features
- Proper input validation and sanitization
- Transaction verification
- Secure session management
- Error logging for debugging

## Setup Instructions

### 1. PayPal Developer Account Setup
1. Go to [PayPal Developer Portal](https://developer.paypal.com)
2. Create a developer account or log in
3. Create a new application
4. Get your Client ID and Client Secret

### 2. Update Configuration
Edit `includes/paypal_config.php`:

```php
// For Sandbox Testing
define('PAYPAL_CLIENT_ID', 'your_sandbox_client_id_here');
define('PAYPAL_CLIENT_SECRET', 'your_sandbox_client_secret_here');

// For Production
// Change PAYPAL_ENVIRONMENT to 'production'
// Update with production credentials
```

### 3. Database Setup
Run the updated `database.sql` file to create the enhanced schema:

```sql
-- The database.sql file now includes:
-- - Enhanced orders table with PayPal fields
-- - New paypal_transactions table
-- - Proper indexes for performance
```

### 4. File Structure
```
project/
├── includes/
│   ├── PayPalService.php          # Modern PayPal service class
│   ├── paypal_config.php          # PayPal configuration
│   └── config.php                 # General site config
├── api/
│   ├── paypal_create_order.php    # Create PayPal order endpoint
│   └── paypal_capture_order.php   # Capture payment endpoint
├── tests/
│   └── paypal_tests.php           # Comprehensive test suite
├── checkout.php                   # Enhanced checkout with PayPal buttons
├── paypal_webhook.php             # Webhook handler for production
└── PAYPAL_INTEGRATION.md          # This documentation
```

## How It Works

### Frontend Flow (checkout.php)
1. User selects PayPal as payment method
2. PayPal button appears with JavaScript SDK
3. User clicks PayPal button
4. PayPal modal opens for login/payment
5. Payment processed instantly without page reload
6. User redirected to success page

### Backend Flow
1. `api/paypal_create_order.php` creates PayPal order
2. PayPal handles payment processing
3. `api/paypal_capture_order.php` captures payment
4. Order saved to database with transaction details
5. Cart cleared and confirmation email sent

### Webhook Flow (Production)
1. PayPal sends webhook notifications
2. `paypal_webhook.php` processes events
3. Order status updated automatically
4. Handles refunds, disputes, etc.

## API Endpoints

### POST /api/paypal_create_order.php
Creates a new PayPal order for the current cart.

**Request:**
```json
{
  "amount": 1234.56,
  "currency": "USD"
}
```

**Response:**
```json
{
  "success": true,
  "order_id": "paypal_order_id",
  "approval_url": "https://paypal.com/approve/..."
}
```

### POST /api/paypal_capture_order.php
Captures payment for an approved PayPal order.

**Request:**
```json
{
  "orderID": "paypal_order_id"
}
```

**Response:**
```json
{
  "success": true,
  "order_number": "ORD-2024-ABC123",
  "transaction_id": "capture_id",
  "amount": "1234.56",
  "redirect_url": "order_success.php?..."
}
```

## Testing

### Run the Test Suite
1. Navigate to `tests/paypal_tests.php` in your browser
2. Review all test results
3. Fix any failing tests before going live

### Manual Testing Steps
1. Add products to cart
2. Go to checkout
3. Select PayPal payment method
4. Verify PayPal button appears
5. Test payment flow (use sandbox credentials)
6. Verify order creation and email notifications

## Database Schema Changes

### Enhanced Orders Table
- Added `payment_transaction_id` for PayPal capture IDs
- Added `payment_details` JSON field for PayPal response data
- Enhanced payment_status enum with more options

### New PayPal Transactions Table
- Detailed PayPal transaction tracking
- Support for webhooks and status updates
- Full PayPal response data storage

## Security Considerations

### Production Checklist
- [ ] Use production PayPal credentials
- [ ] Enable webhook signature verification
- [ ] Use HTTPS for all PayPal interactions
- [ ] Configure proper error handling
- [ ] Set up monitoring and logging

### Security Features
- Input validation and sanitization
- Secure session management
- Transaction verification
- Error logging (but not sensitive data)
- Proper database transactions

## Troubleshooting

### Common Issues
1. **PayPal button doesn't appear**
   - Check JavaScript console for errors
   - Verify PayPal SDK is loading
   - Check client ID configuration

2. **Payment creation fails**
   - Verify PayPal credentials
   - Check network connectivity
   - Review error logs

3. **Database errors**
   - Ensure database schema is updated
   - Check MySQL version compatibility
   - Verify table permissions

### Debug Mode
Enable detailed logging by setting error reporting in `config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Going Live

### Production Setup
1. Change `PAYPAL_ENVIRONMENT` to `'production'`
2. Update with production PayPal credentials
3. Configure webhook URL in PayPal dashboard
4. Test with small real transactions
5. Set up monitoring and alerts

### PayPal Dashboard Configuration
1. Add webhook endpoint: `https://yoursite.com/paypal_webhook.php`
2. Subscribe to these events:
   - PAYMENT.CAPTURE.COMPLETED
   - PAYMENT.CAPTURE.DENIED
   - PAYMENT.CAPTURE.PENDING
   - PAYMENT.CAPTURE.REFUNDED

## Support
For PayPal-specific issues, consult:
- [PayPal Developer Documentation](https://developer.paypal.com/docs/)
- [PayPal JavaScript SDK Guide](https://developer.paypal.com/docs/checkout/)
- [PayPal Orders API v2](https://developer.paypal.com/docs/api/orders/v2/)

## Code Examples

### Basic PayPal Button Implementation
```javascript
paypal.Buttons({
  createOrder: function(data, actions) {
    return actions.order.create({
      purchase_units: [{
        amount: {
          value: '100.00'
        }
      }]
    });
  },
  onApprove: function(data, actions) {
    return actions.order.capture().then(function(details) {
      alert('Transaction completed!');
    });
  }
}).render('#paypal-button-container');
```

### Server-side Order Creation
```php
$paypalService = new PayPalService();
$result = $paypalService->createOrder(100.00, 'USD', 'Test Order');

if ($result['success']) {
    // Redirect to approval URL or handle as needed
    header('Location: ' . $result['approval_url']);
}
```

## Change Log

### Version 2.0 (Current)
- ✅ Implemented PayPal JavaScript SDK v2
- ✅ Created modern PayPal service class
- ✅ Added comprehensive database schema
- ✅ Created webhook handler
- ✅ Added comprehensive test suite
- ✅ Fixed all identified bugs
- ✅ Improved code structure and security

### Previous Version
- Basic PayPal Classic API integration
- Limited error handling
- Placeholder-only implementation
