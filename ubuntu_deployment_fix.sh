#!/bin/bash

echo "=== Ubuntu Outboard Website Deployment Fix ==="
echo "This script fixes upload permissions and database issues for Ubuntu deployment"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[⚠]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

# Get the current directory
SITE_DIR=$(pwd)
echo "Working directory: $SITE_DIR"
echo ""

# 1. Fix directory permissions for Ubuntu/Apache
echo "=== 1. Setting up proper directory permissions ==="

# Create upload directories if they don't exist
mkdir -p uploads/products
mkdir -p uploads/brands  
mkdir -p uploads/categories

# Set proper ownership (assuming www-data is the web server user)
sudo chown -R www-data:www-data uploads/
sudo chown -R www-data:www-data admin/

# Set proper permissions
chmod -R 755 .
chmod -R 775 uploads/
chmod 755 admin/

print_status "Directory permissions set for Ubuntu/Apache"

# 2. Create PHP configuration file for uploads
echo ""
echo "=== 2. Creating PHP upload configuration ==="

cat > .htaccess << 'EOF'
# Enable PHP uploads
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
php_value max_input_time 300

# Security for uploads directory
<Directory "uploads">
    <Files "*.php">
        Require all denied
    </Files>
    <FilesMatch "\.(jpg|jpeg|png|gif)$">
        Require all granted
    </FilesMatch>
</Directory>
EOF

print_status "Created .htaccess with proper PHP settings"

# 3. Create uploads security file
cat > uploads/.htaccess << 'EOF'
# Deny access to PHP files in uploads
<Files *.php>
    Order Allow,Deny
    Deny from all
</Files>

# Allow image files
<FilesMatch "\.(jpg|jpeg|png|gif)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>
EOF

print_status "Created upload security configuration"

# 4. Create database setup verification script
cat > verify_database.php << 'EOF'
<?php
require_once 'includes/config.php';

echo "Testing database connection...\n";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
                   DB_USER, DB_PASS, [
                       PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                       PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                   ]);
    
    echo "✓ Database connection successful\n";
    
    // Test if products table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Products table exists\n";
        
        // Count products
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
        $count = $stmt->fetch()['count'];
        echo "✓ Found $count products in database\n";
    } else {
        echo "✗ Products table missing - run setup.php\n";
    }
    
    // Test brands table
    $stmt = $pdo->query("SHOW TABLES LIKE 'brands'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Brands table exists\n";
    } else {
        echo "✗ Brands table missing - run setup.php\n";
    }
    
    // Test categories table
    $stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Categories table exists\n";
    } else {
        echo "✗ Categories table missing - run setup.php\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. MySQL/MariaDB is running: sudo systemctl status mysql\n";
    echo "2. Database exists: mysql -u root -p -e 'SHOW DATABASES;'\n";
    echo "3. User permissions: mysql -u root -p -e 'SHOW GRANTS FOR \"root\"@\"localhost\";'\n";
}
EOF

print_status "Created database verification script"

echo ""
echo "=== 3. Testing current setup ==="

# Run the database test
php verify_database.php

echo ""
echo "=== 4. Manual steps for Ubuntu deployment ==="
echo ""
print_warning "After uploading to Ubuntu server, run these commands:"
echo "1. sudo apt update && sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql php-gd"
echo "2. sudo systemctl enable apache2 mysql"  
echo "3. sudo systemctl start apache2 mysql"
echo "4. mysql -u root -p -e \"CREATE DATABASE outboard_sales2;\""
echo "5. mysql -u root -p outboard_sales2 < database_backup.sql"
echo "6. sudo chown -R www-data:www-data /var/www/html/your-site/"
echo "7. sudo chmod -R 775 /var/www/html/your-site/uploads/"
echo ""

print_status "Ubuntu deployment preparation complete!"
