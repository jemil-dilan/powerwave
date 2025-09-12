#!/bin/bash

echo "=== PayPal Credentials Setup for Ubuntu ==="
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

print_info() {
    echo -e "${YELLOW}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if PayPal config file exists
CONFIG_FILE="includes/paypal_config.php"

if [ ! -f "$CONFIG_FILE" ]; then
    print_error "PayPal config file not found: $CONFIG_FILE"
    exit 1
fi

echo "This script will help you set up your PayPal credentials for Ubuntu deployment."
echo ""
echo "Before continuing, make sure you have:"
echo "1. A PayPal Developer account (https://developer.paypal.com)"
echo "2. Created an app in PayPal Developer Dashboard"
echo "3. Your Client ID and Client Secret ready"
echo ""

read -p "Do you want to continue? (y/n): " continue_setup

if [ "$continue_setup" != "y" ] && [ "$continue_setup" != "Y" ]; then
    echo "Setup cancelled."
    exit 0
fi

echo ""
echo "Choose your environment:"
echo "1. Sandbox (for testing)"
echo "2. Production (for live transactions)"
read -p "Enter your choice (1 or 2): " env_choice

if [ "$env_choice" = "1" ]; then
    ENVIRONMENT="sandbox"
    CLIENT_ID_PLACEHOLDER="YOUR_SANDBOX_CLIENT_ID_HERE"
    CLIENT_SECRET_PLACEHOLDER="YOUR_SANDBOX_CLIENT_SECRET_HERE"
    echo ""
    print_info "Setting up SANDBOX environment"
elif [ "$env_choice" = "2" ]; then
    ENVIRONMENT="production"  
    CLIENT_ID_PLACEHOLDER="YOUR_PRODUCTION_CLIENT_ID_HERE"
    CLIENT_SECRET_PLACEHOLDER="YOUR_PRODUCTION_CLIENT_SECRET_HERE"
    echo ""
    print_info "Setting up PRODUCTION environment"
    echo ""
    print_error "WARNING: You are setting up PRODUCTION environment!"
    print_error "Make sure you are using LIVE PayPal credentials!"
    echo ""
    read -p "Are you sure you want to continue with PRODUCTION? (y/n): " prod_confirm
    if [ "$prod_confirm" != "y" ] && [ "$prod_confirm" != "Y" ]; then
        echo "Setup cancelled."
        exit 0
    fi
else
    print_error "Invalid choice. Please run the script again."
    exit 1
fi

echo ""
echo "Please enter your PayPal credentials:"
echo ""

# Get Client ID
while true; do
    read -p "PayPal Client ID: " client_id
    if [ -n "$client_id" ] && [ "$client_id" != "$CLIENT_ID_PLACEHOLDER" ]; then
        break
    else
        print_error "Please enter a valid Client ID"
    fi
done

# Get Client Secret
while true; do
    read -s -p "PayPal Client Secret: " client_secret
    echo ""
    if [ -n "$client_secret" ] && [ "$client_secret" != "$CLIENT_SECRET_PLACEHOLDER" ]; then
        break
    else
        print_error "Please enter a valid Client Secret"
    fi
done

echo ""
print_info "Backing up current config..."
cp "$CONFIG_FILE" "${CONFIG_FILE}.backup"

print_info "Updating PayPal configuration..."

# Update the configuration file
if [ "$env_choice" = "1" ]; then
    # Update sandbox credentials
    sed -i "s/YOUR_SANDBOX_CLIENT_ID_HERE/$client_id/g" "$CONFIG_FILE"
    sed -i "s/YOUR_SANDBOX_CLIENT_SECRET_HERE/$client_secret/g" "$CONFIG_FILE"
    # Ensure environment is set to sandbox
    sed -i "s/define('PAYPAL_ENVIRONMENT', 'production');/define('PAYPAL_ENVIRONMENT', 'sandbox');/g" "$CONFIG_FILE"
else
    # Update production credentials
    sed -i "s/YOUR_PRODUCTION_CLIENT_ID_HERE/$client_id/g" "$CONFIG_FILE"
    sed -i "s/YOUR_PRODUCTION_CLIENT_SECRET_HERE/$client_secret/g" "$CONFIG_FILE"
    # Set environment to production
    sed -i "s/define('PAYPAL_ENVIRONMENT', 'sandbox');/define('PAYPAL_ENVIRONMENT', 'production');/g" "$CONFIG_FILE"
fi

print_success "PayPal credentials have been configured!"

echo ""
echo "Testing PayPal configuration..."

# Create a simple test script
cat > test_paypal_config.php << 'EOF'
<?php
require_once 'includes/paypal_config.php';

echo "PayPal Configuration Test\n";
echo "========================\n";
echo "Environment: " . PAYPAL_ENVIRONMENT . "\n";
echo "Client ID: " . substr(PAYPAL_CLIENT_ID, 0, 10) . "...\n";
echo "Base URL: " . PAYPAL_BASE_URL . "\n";

// Test API connection
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . '/v1/oauth2/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['access_token'])) {
        echo "✓ PayPal API connection successful!\n";
    } else {
        echo "✗ PayPal API connection failed - Invalid response\n";
    }
} else {
    echo "✗ PayPal API connection failed - HTTP $httpCode\n";
    if ($response) {
        $error = json_decode($response, true);
        if (isset($error['error_description'])) {
            echo "Error: " . $error['error_description'] . "\n";
        }
    }
}
EOF

# Run the test
php test_paypal_config.php

# Clean up test file
rm test_paypal_config.php

echo ""
print_success "PayPal setup complete!"
echo ""
echo "Next steps:"
echo "1. Test your website's checkout process"
echo "2. Monitor PayPal Developer Dashboard for transactions"
echo "3. When ready for production, re-run this script with production credentials"
echo ""
echo "Backup of original config saved as: ${CONFIG_FILE}.backup"
