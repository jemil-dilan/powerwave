#!/bin/bash

echo "🚀 Starting PowerWave Development Server..."
echo "========================================="

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed or not in PATH"
    exit 1
fi

# Check if MySQL is running
if ! pgrep -x "mysqld" > /dev/null; then
    echo "⚠️  MySQL is not running. Starting MySQL..."
    sudo systemctl start mysql
    if [ $? -ne 0 ]; then
        echo "❌ Failed to start MySQL"
        exit 1
    fi
fi

# Check database connection
php -r "
try {
    \$pdo = new PDO('mysql:host=localhost;dbname=outboard_sales2', 'root', 'powerwave123');
    echo '✅ Database connection successful\n';
} catch(Exception \$e) {
    echo '❌ Database connection failed: ' . \$e->getMessage() . '\n';
    exit(1);
}
"

if [ $? -ne 0 ]; then
    echo "❌ Database check failed"
    exit 1
fi

echo ""
echo "✅ All checks passed!"
echo ""
echo "📱 Starting PHP development server on localhost:8000..."
echo "🌐 Website will be available at: http://localhost:8000"
echo ""
echo "📄 Quick Links:"
echo "   • Homepage: http://localhost:8000/index.php"
echo "   • Products: http://localhost:8000/products.php"
echo "   • Debug:    http://localhost:8000/debug_payments.php"
echo ""
echo "⛔ Press Ctrl+C to stop the server"
echo ""

# Start the PHP development server
php -S localhost:8000