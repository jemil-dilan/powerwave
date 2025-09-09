<?php
// File: includes/crypto_config.php
// Cryptocurrency Payment Configuration

// Bitcoin Configuration
define('BITCOIN_WALLET_ADDRESS', 'YOUR_BITCOIN_WALLET_ADDRESS_HERE');
define('BITCOIN_NETWORK', 'mainnet'); // 'mainnet' or 'testnet'

// USDT Configuration (Ethereum/Polygon/Tron - specify which network)
define('USDT_WALLET_ADDRESS', 'YOUR_USDT_WALLET_ADDRESS_HERE');
define('USDT_NETWORK', 'ethereum'); // 'ethereum', 'polygon', or 'tron'

// Exchange rate API (optional - for real-time conversion)
define('CRYPTO_API_KEY', 'YOUR_API_KEY_HERE'); // CoinGecko/CoinAPI key
define('CRYPTO_API_URL', 'https://api.coingecko.com/api/v3');

// Payment confirmation settings
define('CRYPTO_CONFIRMATION_BLOCKS', 3); // Number of confirmations required
define('CRYPTO_PAYMENT_TIMEOUT', 3600); // 1 hour timeout for payment

/**
 * Get current cryptocurrency prices in USD
 */
function getCryptoPrices(): array
{
    try {
        // Using CoinGecko free API (no key required)
        $url = CRYPTO_API_URL . '/simple/price?ids=bitcoin,tether&vs_currencies=usd';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PowerWave Outboards');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            return [
                'bitcoin' => $data['bitcoin']['usd'] ?? 0,
                'usdt' => $data['tether']['usd'] ?? 1, // USDT should be ~$1
                'last_updated' => time()
            ];
        }
    } catch (Exception $e) {
        error_log('Crypto price fetch error: ' . $e->getMessage());
    }

    // Fallback prices if API fails
    return [
        'bitcoin' => 45000, // Fallback BTC price
        'usdt' => 1, // USDT is typically $1
        'last_updated' => time(),
        'fallback' => true
    ];
}

/**
 * Calculate cryptocurrency amount needed for USD total
 */
function calculateCryptoAmount($usdAmount, $cryptoType) {
    $prices = getCryptoPrices();

    if ($cryptoType === 'bitcoin') {
        return round($usdAmount / $prices['bitcoin'], 8); // Bitcoin has 8 decimal places
    } elseif ($cryptoType === 'usdt') {
        return round($usdAmount / $prices['usdt'], 2); // USDT typically has 2-6 decimals, using 2
    }

    return 0;
}
?>