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

/**
 * Récupère les prix actuels des cryptomonnaies depuis une API ou utilise des valeurs par défaut
 * @return array Prix des cryptomonnaies et horodatage
 */
function getCryptoPrices() {
    try {
        // Appel API pour obtenir les prix en direct (à implémenter selon votre API)
        $response = @file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,tether&vs_currencies=usd');

        if ($response === false) {
            throw new Exception('Unable to fetch crypto prices');
        }

        $data = json_decode($response, true);

        if (!$data) {
            throw new Exception('Invalid API response');
        }

        return [
            'bitcoin' => $data['bitcoin']['usd'] ?? 50000,
            'usdt' => $data['tether']['usd'] ?? 1,
            'last_updated' => time(),
            'fallback' => false
        ];

    } catch (Exception $e) {
        // En cas d'erreur, utiliser des valeurs par défaut
        return [
            'bitcoin' => 50000, // Valeur par défaut pour Bitcoin
            'usdt' => 1,        // USDT est stable à ~1$
            'last_updated' => time(),
            'fallback' => true  // Indique l'utilisation des valeurs par défaut
        ];
    }
}

/**
 * Calcule le montant en crypto pour un montant donné en USD
 * @param float $amount Montant en USD
 * @param string $crypto Type de crypto ('bitcoin' ou 'usdt')
 * @return float Montant en crypto
 */
function calculateCryptoAmount(float $amount, string $crypto): float
{
    $prices = getCryptoPrices();
    $cryptoPrice = $prices[$crypto] ?? 1;
    return $amount / $cryptoPrice;
}

?>
