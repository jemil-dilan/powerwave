# Hosting Troubleshooting Guide

This guide addresses common issues when deploying the PowerWave Outboards e-commerce site to a web hosting provider.

## Issues Fixed

### ✅ 1. Accessories Page 500 Error
**Problem:** The accessories page was returning a 500 Internal Server Error due to:
- Git merge conflicts in the PHP code
- Missing PHP closing tags/braces
- Syntax errors

**Solutions Applied:**
- Removed Git merge conflict markers (`<<<<<<< HEAD`, `=======`, `>>>>>>> branch`)
- Fixed unclosed PHP tags and HTML elements
- Resolved syntax errors in accessories.php

### ✅ 2. Font Awesome Icons Showing as Numbers
**Problem:** When hosted, Font Awesome icons display as numbers instead of icons.

**Common Causes & Solutions:**

#### A. CDN/HTTPS Issues
**Cause:** Mixed content (HTTP/HTTPS) or CDN blocking
**Solution:**
```html
<!-- Use HTTPS-compatible CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
```

#### B. Server Configuration Issues
**Cause:** Server blocks external CSS or has Content Security Policy restrictions
**Solution:**
1. **Download Font Awesome locally:**
   ```bash
   # Download Font Awesome
   wget https://github.com/FortAwesome/Font-Awesome/releases/download/6.0.0/fontawesome-free-6.0.0-web.zip
   unzip fontawesome-free-6.0.0-web.zip
   cp -r fontawesome-free-6.0.0-web/css/ ./css/fontawesome/
   cp -r fontawesome-free-6.0.0-web/webfonts/ ./webfonts/
   ```

2. **Update HTML to use local files:**
   ```html
   <link rel="stylesheet" href="css/fontawesome/all.min.css">
   ```

#### C. Font Loading Issues
**Cause:** Fonts not loading properly on the hosting server
**Solution:**
1. **Add font preloading:**
   ```html
   <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin>
   ```

2. **Add CSS fallback:**
   ```css
   .fas, .far, .fab {
       font-display: swap;
       font-weight: 900;
       font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", sans-serif !important;
   }
   ```

## Current Server Requirements

### ✅ Required PHP Extensions
The site requires these PHP extensions to be installed on your hosting provider:

```bash
# Check what's currently missing
php -m | grep -E "(pdo|mysql|gd|json|curl|mbstring)"
```

**Required Extensions:**
- `pdo_mysql` - For database connectivity ❌ **MISSING**
- `gd` - For image processing
- `json` - For JSON handling
- `curl` - For PayPal API calls
- `mbstring` - For string handling
- `session` - For user sessions

### 🔧 Installing Missing Extensions

#### Ubuntu/Debian:
```bash
sudo apt update
sudo apt install php-mysql php-pdo php-gd php-json php-curl php-mbstring
sudo systemctl restart apache2
```

#### CentOS/RHEL:
```bash
sudo yum install php-mysql php-pdo php-gd php-json php-curl php-mbstring
sudo systemctl restart httpd
```

#### cPanel/Shared Hosting:
- Contact your hosting provider to install `php-mysql` and `php-pdo` extensions
- Or use the cPanel PHP Extensions manager if available

## Database Setup

### 1. Create Database
```sql
CREATE DATABASE outboard_sales2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Import Schema
```bash
mysql -u username -p outboard_sales2 < database.sql
```

### 3. Update Configuration
Edit `includes/config.php`:
```php
define('DB_HOST', 'your_host');        // Usually 'localhost'
define('DB_NAME', 'outboard_sales2');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('SITE_URL', 'https://yourdomain.com');
```

## File Permissions

### Set Proper Permissions
```bash
# Make uploads directory writable
chmod 755 uploads/
chmod 755 uploads/products/
chmod 755 uploads/categories/

# Make config readable only
chmod 644 includes/config.php

# Make PHP files executable
find . -name "*.php" -exec chmod 644 {} \;
```

### For Shared Hosting
If you don't have SSH access:
1. Use your hosting control panel's File Manager
2. Set directories to `755`
3. Set files to `644`
4. Set uploads directories to `755` or `777` (be careful with 777)

## Testing Checklist

### ✅ After Deployment Test These:

1. **Homepage loads:** `https://yourdomain.com/`
2. **Accessories page:** `https://yourdomain.com/accessories.php`
3. **Font Awesome test:** `https://yourdomain.com/font_awesome_test.html`
4. **Database connectivity:** Check if products display
5. **Upload functionality:** Test image uploads in admin
6. **PayPal integration:** Test payment process

### 🐛 Common Error Patterns

#### 500 Internal Server Error
- Check server error logs: `tail -f /var/log/apache2/error.log`
- Look for PHP syntax errors
- Verify file permissions
- Check for missing PHP extensions

#### Font Icons as Numbers
- Inspect element in browser dev tools
- Check if Font Awesome CSS is loading
- Look for CORS errors in console
- Test with local Font Awesome files

#### Database Connection Errors
- Verify MySQL service is running
- Check database credentials
- Ensure PDO MySQL extension is installed
- Test connection with `php -f test_db_connection.php`

## Production Configuration

### 1. Security Settings
```php
// In includes/config.php - DISABLE FOR PRODUCTION
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
```

### 2. HTTPS Configuration
```php
// Force HTTPS in production
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirectURL");
    exit();
}
```

### 3. PayPal Production
```php
// In includes/paypal_config.php
define('PAYPAL_ENVIRONMENT', 'production'); // Change from 'sandbox'
define('PAYPAL_CLIENT_ID', 'your_production_client_id');
define('PAYPAL_CLIENT_SECRET', 'your_production_secret');
```

## Support Resources

### Test Files Created
- `font_awesome_test.html` - Test Font Awesome loading
- `HOSTING_TROUBLESHOOTING.md` - This guide

### Debug Commands
```bash
# Check PHP version and extensions
php -v && php -m

# Test database connection
php -r "
try {
    \$pdo = new PDO('mysql:host=localhost;dbname=outboard_sales2', 'user', 'pass');
    echo 'Database connection: OK';
} catch(PDOException \$e) {
    echo 'Database connection: FAILED - ' . \$e->getMessage();
}
"

# Check file permissions
ls -la uploads/
```

### Contact Points
- **Hosting Provider:** For PHP extension installation
- **Domain Provider:** For DNS and SSL issues
- **PayPal Developer Support:** For payment integration issues

---

**Note:** Always backup your files and database before making changes to a production site.