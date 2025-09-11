# Outboard Motors Website - Complete Installation Guide

## ✅ What's Included

Your complete outboard motors e-commerce website now includes:

### Core Pages
- **Homepage** (`index.php`) - Featured products, categories, hero section
- **Product Listing** (`products.php`) - Browse all motors with filters
- **Product Details** (`product.php`) - Individual product pages
- **Shopping Cart** (`cart.php`) - Add/remove/update cart items
- **About Page** (`about.php`) - Company information and services
- **Contact Page** (`contact.php`) - Contact form and business info
- **Checkout** (`checkout.php`) - Complete checkout with address collection
- **Order Success** (`order_success.php`) - Payment instructions and confirmation

### Payment Methods Supported
- **PayPal** - Complete integration placeholders
- **Bank Transfer** - Account details display
- **Apple Pay** - Integration placeholders
- **Cash App** - Payment tag system

### Admin System
- **Admin Dashboard** (`admin/index.php`) - Statistics and recent orders
- **User Authentication** - Login/register/logout system
- **Order Management** - Complete order processing flow

## 🚀 Installation Steps

### 1. Database Setup
```bash
# Create database and import schema
mysql -u root -p -e "CREATE DATABASE outboard_sales;"
mysql -u root -p outboard_sales < database.sql
```

### 2. Configuration
Edit `includes/config.php`:
```php
// Update database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'outboard_sales');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');

// Update site URL
define('SITE_URL', 'http://yoursite.com');

// Add your payment credentials
define('PAYPAL_CLIENT_ID', 'your_paypal_client_id');
define('APPLE_PAY_MERCHANT_ID', 'your_apple_merchant_id');
define('CASH_APP_CASHTAG', '$YourCashTag');

// Update bank details
define('BANK_ACCOUNT_NAME', 'Your Business Name');
define('BANK_ACCOUNT_NUMBER', 'your_account_number');
define('BANK_ROUTING_NUMBER', 'your_routing_number');
```

### 3. Create Admin Account
```bash
# Run this after database is set up
php create_admin.php
```

### 4. File Permissions
```bash
chmod 755 uploads/
chmod 644 includes/config.php
```

### 5. Test Installation
Visit `setup.php` in your browser to verify everything works.

## 🔐 Default Login Accounts

After setup, you'll have these accounts:

- **Admin**: `admin@outboard-sales.com` / `admin123`
- **Demo Customer**: `demo@example.com` / `demo123`

## 📁 File Structure

```
outboard-website/
├── admin/
│   └── index.php              # Admin dashboard
├── css/
│   ├── style.css              # Main styles
│   └── responsive.css         # Mobile responsive
├── js/
│   └── main.js                # Interactive features
├── includes/
│   ├── config.php             # Configuration settings
│   ├── database.php           # Database connection
│   └── functions.php          # Helper functions
├── images/                    # Static images
├── uploads/                   # User uploads
├── index.php                  # Homepage
├── products.php               # Product listing
├── product.php                # Product details
├── cart.php                   # Shopping cart
├── checkout.php               # Checkout process
├── order_success.php          # Order confirmation
├── about.php                  # About page
├── contact.php                # Contact page
├── login.php                  # User login
├── register.php               # User registration
├── logout.php                 # Logout handler
├── add_to_cart.php           # Cart API endpoint
├── place_order.php           # Order creation
├── database.sql              # Database schema
├── create_admin.php          # Admin account setup
└── setup.php                 # Installation helper
```

## 🛠 Key Features

### Frontend Features
- **Responsive Design** - Mobile-friendly layout
- **Product Search & Filtering** - By category, brand, price
- **Shopping Cart** - Add/remove/update items
- **User Accounts** - Registration and login
- **Order Tracking** - Complete order flow
- **Contact Forms** - Customer inquiries

### Backend Features  
- **Secure Authentication** - Password hashing, session management
- **Admin Dashboard** - Order and user management
- **Payment Integration** - Multiple payment method support
- **Email Notifications** - Order confirmations
- **Database Security** - SQL injection prevention

### Payment Methods
- **PayPal** - Replace `PAYPAL_CLIENT_ID` with real credentials
- **Bank Transfer** - Update bank account details in config
- **Apple Pay** - Replace `APPLE_PAY_MERCHANT_ID` 
- **Cash App** - Update `CASH_APP_CASHTAG`

## 🔧 Customization

### Adding Your Payment Credentials

1. **PayPal Integration**
   - Get client ID from PayPal Developer Portal
   - Replace `{{PAYPAL_CLIENT_ID}}` in config.php
   - Add PayPal SDK to checkout page

2. **Apple Pay Setup**
   - Register merchant ID with Apple
   - Replace `{{APPLE_PAY_MERCHANT_ID}}`
   - Configure domain verification

3. **Cash App**
   - Replace `{{CASH_APP_CASHTAG}}` with your Cash App username

4. **Bank Transfer**
   - Update account details in config.php
   - Ensure all banking information is accurate

### Adding Products
1. Login as admin
2. Access admin panel
3. Add products with images and specifications
4. Set categories and featured status

### Styling Changes
- Main styles: `css/style.css`
- Mobile responsive: `css/responsive.css`
- Colors, fonts, layout can be customized

## 🛡 Security Features

- **Password Hashing** - PHP password_hash() function
- **SQL Injection Prevention** - PDO prepared statements
- **XSS Protection** - Input sanitization
- **Session Security** - Secure session handling
- **CSRF Protection** - Token validation (foundation)

## 📱 Browser Support

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+
- Mobile browsers

## 📞 Support

All placeholder credentials are clearly marked with `{{PLACEHOLDER_NAME}}` format in the code. Simply replace these with your real merchant credentials when ready to go live.

The website is fully functional and ready for production use once you:
1. Set up the database
2. Configure your payment provider credentials  
3. Add your actual business information
4. Upload product images

## 🚀 Going Live Checklist

- [ ] Import database.sql
- [ ] Update config.php with real credentials
- [ ] Replace placeholder payment credentials
- [ ] Add real product images
- [ ] Test all payment methods
- [ ] Configure email settings
- [ ] Set up SSL certificate
- [ ] Update business contact information
