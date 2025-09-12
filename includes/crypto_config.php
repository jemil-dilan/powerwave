<?php
// File: includes/crypto_config.php
// Cryptocurrency Payment Configuration using Coinbase Commerce

// 1. Get your API Key from Coinbase Commerce: https://commerce.coinbase.com/settings/api
define('COINBASE_COMMERCE_API_KEY', '14fed5cd-921a-4f43-9559-5df8c04c1310');

// 2. Set up a webhook endpoint in your Coinbase Commerce settings: https://commerce.coinbase.com/settings/notifications
//    The URL should be: http://yoursite.com/crypto_webhook.php
// 3. Add the 'Webhook shared secret' provided by Coinbase here.
define('COINBASE_COMMERCE_WEBHOOK_SECRET', 'YOUR_COINBASE_WEBHOOK_SECRET_HERE');

// Coinbase Commerce API URL
define('COINBASE_COMMERCE_API_URL', 'https://api.commerce.coinbase.com');

// NOTE: The functions getCryptoPrices() and calculateCryptoAmount() are now defined in includes/functions.php
// to avoid function redeclaration errors.

?>
