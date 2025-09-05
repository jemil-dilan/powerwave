# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

PowerWave Outboards is a complete PHP-based e-commerce platform for selling outboard motors. The project features modern PayPal integration, comprehensive admin management, and a responsive frontend built with vanilla PHP, MySQL, HTML5, CSS3, and JavaScript.

**Technology Stack:**
- **Backend**: PHP 8+ with PDO
- **Database**: MySQL 5.7+ with enhanced schema
- **Frontend**: HTML5, CSS3, JavaScript ES6+ (vanilla)
- **Payment**: PayPal JavaScript SDK v2 + Orders API v2
- **Architecture**: MVC-inspired structure with clean separation

## Common Development Commands

### Database Setup
```bash
# Create and import database
mysql -u root -p -e "CREATE DATABASE outboard_sales2;"
mysql -u root -p outboard_sales2 < database.sql

# Create admin account
php create_admin.php
```

### Local Development
```bash
# Start PHP built-in server (for quick testing)
php -S localhost:8000

# Set proper file permissions (Linux/Mac)
chmod 755 uploads/
chmod 644 includes/config.php

# View error logs
tail -f error.log
```

### Testing Commands
```bash
# Run PayPal integration tests
# Navigate to: http://localhost/tests/paypal_tests.php

# Test individual components
php admin/test_product_image.php  # Image upload testing
php test_login.php                # Authentication testing
```

### Production Deployment
```bash
# Set production PayPal environment
# Edit includes/paypal_config.php: PAYPAL_ENVIRONMENT = 'production'

# Disable debug mode
# Edit includes/config.php: error_reporting(0); ini_set('display_errors', 0);

# Verify SSL and HTTPS setup for PayPal
curl -I https://yoursite.com/api/paypal_create_order.php
```

## Architecture Overview

### Core Architecture Pattern
The application follows a **functional MVC-inspired** pattern with clean separation:

- **Models**: Database interactions handled by `Database` singleton class with prepared statements
- **Views**: PHP templates with embedded logic, consistent header/footer includes  
- **Controllers**: Page-level PHP files that orchestrate data and render views
- **Services**: Specialized classes like `PayPalService` for external integrations

### Directory Structure
```
powerwave/
├── includes/           # Core PHP logic and configuration
│   ├── config.php     # Site configuration and constants
│   ├── database.php   # Singleton database class with PDO
│   ├── functions.php  # Utility functions (auth, cart, products)
│   ├── PayPalService.php    # Modern PayPal Orders API v2 implementation
│   └── paypal_config.php    # PayPal-specific configuration
├── admin/             # Admin panel with dedicated authentication
│   ├── index.php      # Dashboard with statistics and recent activity
│   ├── products.php   # Product CRUD operations
│   ├── orders.php     # Order management system
│   └── users.php      # User management
├── api/               # RESTful API endpoints
│   ├── paypal_create_order.php   # PayPal order creation endpoint
│   └── paypal_capture_order.php  # PayPal payment capture endpoint
├── css/               # Styling with mobile-first responsive design
├── js/                # Vanilla JavaScript for interactivity
├── uploads/           # User-uploaded files (products, categories)
└── images/            # Static assets and placeholders
```

### Database Architecture
The database uses a **normalized relational structure** optimized for e-commerce:

**Core Tables:**
- `products` - Outboard motors with detailed specifications (horsepower, stroke, fuel type, etc.)
- `users` - Customer and admin accounts with role-based access
- `orders` - Order lifecycle management with PayPal integration fields
- `cart` - Session and user-based shopping cart persistence
- `categories/brands` - Product organization and filtering

**PayPal Integration Tables:**
- `orders.payment_transaction_id` - PayPal capture IDs
- `orders.payment_details` - JSON storage for PayPal responses
- `paypal_transactions` - Detailed PayPal transaction tracking with webhook support

### Authentication & Security
- **Session Management**: Secure PHP sessions with timeout handling
- **Password Security**: PHP `password_hash()` with `PASSWORD_DEFAULT`
- **SQL Injection Prevention**: All queries use PDO prepared statements
- **XSS Protection**: Input sanitization via `htmlspecialchars()` throughout
- **Role-Based Access**: Customer/Admin roles with `requireAdmin()` guards

### PayPal Integration Architecture
Modern **PayPal JavaScript SDK v2** implementation with server-side Orders API v2:

**Frontend Flow:**
1. PayPal button renders with JavaScript SDK
2. User payment happens in PayPal modal (no redirects)
3. Frontend captures approval and calls backend APIs

**Backend Flow:**
1. `api/paypal_create_order.php` - Creates PayPal order via REST API
2. `api/paypal_capture_order.php` - Captures payment and processes order
3. `PayPalService` class handles all PayPal API communication
4. Database updated with transaction details and order status

## Key Development Patterns

### Database Operations
Always use the Database singleton with prepared statements:
```php
$db = Database::getInstance();
$products = $db->fetchAll(
    "SELECT p.*, b.name as brand_name FROM products p JOIN brands b ON p.brand_id = b.id WHERE p.category_id = ?",
    [$categoryId]
);
```

### Authentication Checks
Use function-based authentication throughout:
```php
requireLogin();    # Redirects to login if not authenticated
requireAdmin();    # Redirects to homepage if not admin
isLoggedIn();      # Boolean check for authentication state
getCurrentUser();  # Returns current user data array
```

### Error Handling
Consistent error handling with user feedback:
```php
try {
    // Database operations
} catch (Exception $e) {
    error_log('Specific operation failed: ' . $e->getMessage());
    showMessage('User-friendly error message', 'error');
    redirect('appropriate-page.php');
}
```

### Cart Management
Session-aware cart that persists for both logged-in and guest users:
```php
addToCart($productId, $quantity, $userId);  # $userId = null for guests
$items = getCartItems($userId);             # Retrieves cart for user or session
$total = getCartTotal($userId);             # Calculates cart total
```

### File Upload Pattern
Use the centralized upload function for consistency:
```php
$result = handleImageUpload($_FILES['image'], 'products');
if ($result['success']) {
    $filename = $result['filename'];  # Use this for database storage
}
```

## Configuration Management

### Environment-Specific Settings
Key configuration areas in `includes/config.php`:

**Database Configuration:**
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'outboard_sales2');  
define('DB_USER', 'root');
define('DB_PASS', '');
```

**PayPal Configuration:**
- Sandbox credentials in `includes/paypal_config.php`
- Environment toggle: `PAYPAL_ENVIRONMENT` ('sandbox' or 'production')
- Client ID/Secret management with placeholder system

**Site Configuration:**
```php
define('SITE_URL', 'http://localhost/outboard-website');
define('SITE_NAME', 'PowerWave outboards');
define('TAX_RATE', 0.08);           # 8% tax rate
define('SHIPPING_RATE', 99.99);     # Flat shipping rate
```

### Development vs Production
- **Development**: Error reporting enabled, sandbox PayPal, localhost URLs
- **Production**: Error reporting off, production PayPal credentials, HTTPS required

## Testing Strategy

### PayPal Integration Testing
Navigate to `tests/paypal_tests.php` for comprehensive test suite covering:
- PayPal configuration validation
- Service class functionality  
- Database schema verification
- API endpoint testing
- Helper function validation

### Manual Testing Workflow
1. **Product Management**: Add products via admin, verify frontend display
2. **Cart Functionality**: Add/remove items, test session persistence
3. **Checkout Process**: Complete orders with different payment methods
4. **PayPal Integration**: Test sandbox payments end-to-end
5. **Admin Functions**: Verify order management and user administration

### Database Testing
```php
# Test database connection
php -r "require 'includes/database.php'; echo 'DB connected: ' . (Database::getInstance() ? 'Yes' : 'No');"

# Verify PayPal schema
mysql -u root -p outboard_sales2 -e "DESCRIBE paypal_transactions;"
```

## PayPal Integration Details

### Sandbox Setup
1. PayPal Developer account at developer.paypal.com
2. Create sandbox application
3. Update `PAYPAL_CLIENT_ID` and `PAYPAL_CLIENT_SECRET` in `includes/paypal_config.php`
4. Test with sandbox buyer accounts

### Production Deployment
1. Switch `PAYPAL_ENVIRONMENT` to 'production'
2. Update with production credentials
3. Configure webhook URL: `https://yoursite.com/paypal_webhook.php`
4. Enable webhook events: PAYMENT.CAPTURE.COMPLETED, PAYMENT.CAPTURE.DENIED
5. Verify SSL certificate and HTTPS enforcement

### API Endpoints
- **POST** `/api/paypal_create_order.php` - Creates PayPal order from cart
- **POST** `/api/paypal_capture_order.php` - Captures payment and processes order
- **POST** `/paypal_webhook.php` - Production webhook handler

## Common Issues & Solutions

### PayPal Integration Issues
- **Button not appearing**: Check JavaScript console, verify PayPal SDK loading
- **Payment creation fails**: Verify credentials, check network connectivity
- **Amount mismatch**: Ensure frontend and backend calculations match exactly

### Database Issues
- **Connection failures**: Verify credentials in `includes/config.php`
- **Missing tables**: Re-run `database.sql` import
- **Permission errors**: Check MySQL user permissions for database

### File Upload Issues
- **Upload failures**: Verify `uploads/` directory exists with proper permissions (755)
- **Image not displaying**: Check `getProductImageUrl()` function and path generation
- **Size limits**: Configure `MAX_FILE_SIZE` in `includes/config.php`

### Session Issues
- **Cart not persisting**: Ensure `session_start()` called before any output
- **Login issues**: Verify password hashing matches between registration and login
- **Admin access**: Check role assignment in users table

## Security Considerations

### Input Validation
All user input passes through `sanitizeInput()` function before display
Database queries use prepared statements exclusively
File uploads validated for type, size, and content

### PayPal Security
- Transaction verification before processing orders  
- Webhook signature verification (production)
- Secure credential storage (not in version control)
- HTTPS required for all PayPal interactions

### Session Security
- Session timeout configuration (`SESSION_TIMEOUT`)
- CSRF token framework available for forms
- Role-based access control with function guards
- Secure password storage with modern hashing

This architecture provides a solid foundation for e-commerce development while maintaining security, performance, and maintainability standards.
