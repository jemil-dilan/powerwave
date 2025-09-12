# Ubuntu Deployment Guide for Outboard Website

This guide provides complete instructions to deploy your outboard motor website on Ubuntu, fixing all upload issues, database problems, and setting up PayPal integration.

## 🚀 Quick Fix Summary

I've identified and fixed these issues:
- ✅ **Upload directory permissions** - Fixed for Ubuntu/Apache
- ✅ **Database connection problems** - Complete setup script provided  
- ✅ **Image upload failures** - Enhanced error handling and permissions
- ✅ **PayPal integration** - Easy credential setup system

## 📋 Prerequisites

- Ubuntu server (18.04+)
- Root or sudo access
- PayPal Developer account

## 🔧 Step 1: Server Setup

### Install Required Packages
```bash
sudo apt update
sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql php-gd php-curl php-zip php-mbstring
```

### Enable Apache Modules
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Secure MySQL Installation
```bash
sudo mysql_secure_installation
```

## 📁 Step 2: Deploy Website Files

### Upload Files to Server
```bash
# Upload your website files to /var/www/html/your-site/
# Or use git clone, rsync, etc.

# Set proper ownership
sudo chown -R www-data:www-data /var/www/html/your-site/
sudo chmod -R 755 /var/www/html/your-site/
sudo chmod -R 775 /var/www/html/your-site/uploads/
```

## 🗄️ Step 3: Fix Database Issues

### Run Database Fix Script
```bash
cd /var/www/html/your-site/
php fix_database_ubuntu.php
```

This script will:
- ✅ Test database connection
- ✅ Create missing database and tables
- ✅ Insert sample data (brands, categories)
- ✅ Verify all functionality

### Manual Database Setup (if needed)
```sql
sudo mysql -u root -p

CREATE DATABASE outboard_sales2;
CREATE USER 'root'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON outboard_sales2.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 🔐 Step 4: Fix Upload Permissions

### Run Permission Fix Script
```bash
chmod +x ubuntu_deployment_fix.sh
./ubuntu_deployment_fix.sh
```

### Manual Permission Fix
```bash
# Create upload directories
mkdir -p uploads/products uploads/brands uploads/categories

# Set proper permissions
sudo chown -R www-data:www-data uploads/
sudo chmod -R 775 uploads/

# Test upload functionality  
echo "test" | sudo -u www-data tee uploads/test.txt
rm uploads/test.txt
```

## 💳 Step 5: Setup PayPal Integration

### Get PayPal Credentials

1. Go to https://developer.paypal.com
2. Log in with your PayPal account
3. Go to "My Apps & Credentials"
4. Create a new app:
   - **App Name**: Outboard Motor Store
   - **Merchant**: Select your account
   - **Features**: Accept Payments
5. Copy your **Client ID** and **Client Secret**

### Configure PayPal Credentials

#### Option A: Use Automated Script
```bash
chmod +x setup_paypal_ubuntu.sh
./setup_paypal_ubuntu.sh
```

#### Option B: Manual Configuration
```bash
nano includes/paypal_config.php

# Replace these lines:
define('PAYPAL_CLIENT_ID', 'YOUR_SANDBOX_CLIENT_ID_HERE');
define('PAYPAL_CLIENT_SECRET', 'YOUR_SANDBOX_CLIENT_SECRET_HERE');

# With your actual credentials:
define('PAYPAL_CLIENT_ID', 'AQmYourActualClientIDHere');
define('PAYPAL_CLIENT_SECRET', 'ELYourActualClientSecretHere');
```

### Test PayPal Configuration
```bash
php -r "
require_once 'includes/paypal_config.php';
\$ch = curl_init(PAYPAL_BASE_URL . '/v1/oauth2/token');
curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt(\$ch, CURLOPT_POST, true);
curl_setopt(\$ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
curl_setopt(\$ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
\$response = curl_exec(\$ch);
\$httpCode = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
curl_close(\$ch);
echo 'PayPal API Status: ' . (\$httpCode === 200 ? 'SUCCESS' : 'FAILED') . PHP_EOL;
"
```

## ⚙️ Step 6: Apache Configuration

### Create Virtual Host (Optional)
```bash
sudo nano /etc/apache2/sites-available/outboard.conf
```

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/your-site
    
    <Directory /var/www/html/your-site>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Increase upload limits
    php_admin_value upload_max_filesize 10M
    php_admin_value post_max_size 10M
    php_admin_value max_execution_time 300
    
    ErrorLog ${APACHE_LOG_DIR}/outboard_error.log
    CustomLog ${APACHE_LOG_DIR}/outboard_access.log combined
</VirtualHost>
```

```bash
sudo a2ensite outboard.conf
sudo systemctl restart apache2
```

## 🧪 Step 7: Test Everything

### Test Upload Functionality
1. Go to `/admin/add_product.php`
2. Try uploading an image
3. Should work without "upload directory not writable" errors

### Test Database Operations  
1. Create a new product
2. Should save to database successfully
3. Check products appear on frontend

### Test PayPal Integration
1. Add items to cart
2. Go to checkout
3. PayPal button should appear and function

## 🔍 Troubleshooting

### Common Issues and Solutions

#### Upload Directory Not Writable
```bash
sudo chown -R www-data:www-data uploads/
sudo chmod -R 775 uploads/
sudo systemctl restart apache2
```

#### Database Connection Failed
```bash
sudo systemctl status mysql
sudo systemctl start mysql
mysql -u root -p -e "SHOW DATABASES;"
```

#### PayPal API Errors
```bash
# Check credentials
grep -r "PAYPAL_CLIENT" includes/paypal_config.php

# Test connectivity
curl -v https://api-m.sandbox.paypal.com/v1/oauth2/token
```

#### PHP Errors
```bash
# Check PHP error log
sudo tail -f /var/log/apache2/error.log

# Check PHP configuration
php -m | grep -E "(mysql|gd|curl)"
```

### Log Files to Monitor
```bash
# Apache error log
sudo tail -f /var/log/apache2/error.log

# Apache access log  
sudo tail -f /var/log/apache2/access.log

# MySQL error log
sudo tail -f /var/log/mysql/error.log
```

## 🔒 Security Considerations

### Production Deployment
1. **Change PayPal to Production Mode**:
   ```bash
   nano includes/paypal_config.php
   # Change: define('PAYPAL_ENVIRONMENT', 'production');
   ```

2. **Secure File Permissions**:
   ```bash
   find . -type f -exec chmod 644 {} \;
   find . -type d -exec chmod 755 {} \;
   chmod -R 775 uploads/
   ```

3. **Secure Database**:
   - Use strong database passwords
   - Consider creating separate database user
   - Enable MySQL firewall if available

4. **Enable HTTPS**:
   ```bash
   sudo apt install certbot python3-certbot-apache
   sudo certbot --apache -d your-domain.com
   ```

## 📞 Support

If you encounter issues:

1. **Check the log files** mentioned above
2. **Verify all steps** were completed
3. **Test each component** individually
4. **Check file permissions** are correct

### Quick Diagnostic Commands
```bash
# Check web server status
sudo systemctl status apache2

# Check database status  
sudo systemctl status mysql

# Check PHP modules
php -m

# Check file permissions
ls -la uploads/

# Test database connection
php fix_database_ubuntu.php

# Test PayPal configuration
./setup_paypal_ubuntu.sh
```

---

## ✅ Deployment Checklist

- [ ] Ubuntu server setup complete
- [ ] Website files uploaded and permissions set
- [ ] Database created and configured
- [ ] Upload directories working
- [ ] PayPal credentials configured
- [ ] Apache virtual host configured (if needed)
- [ ] All functionality tested
- [ ] Error logs monitored
- [ ] Security measures implemented

Your outboard website should now be fully functional on Ubuntu! 🚀
