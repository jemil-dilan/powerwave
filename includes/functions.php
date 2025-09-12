<?php
require_once 'database.php';

// Démarrer la session si nécessaire (assurer disponibilité de $_SESSION)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security functions
function sanitizeInput($data): string
{
    // Gérer les tableaux et types non-string pour éviter warnings
    if (is_array($data)) {
        // Si un tableau est passé par erreur, retourner chaîne vide pour compatibilité
        return '';
    }
    if ($data === null) {
        return '';
    }
    if (!is_string($data)) {
        $data = (string)$data;
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Génère (ou retourne) le token CSRF.
 * Capturer toute exception de random_bytes et revenir à un fallback si nécessaire.
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            // Fallback en cas d'impossibilité d'utiliser random_bytes
            $_SESSION['csrf_token'] = bin2hex(md5(uniqid((string)time(), true)));
        }
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }

    $expire = defined('CSRF_TOKEN_EXPIRE') ? CSRF_TOKEN_EXPIRE : 3600;

    if (time() - $_SESSION['csrf_token_time'] > $expire) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], (string)$token);
}

function requireCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($token)) {
            showMessage('Invalid security token. Please try again.', 'error');
            redirect($_SERVER['PHP_SELF']);
        }
    }
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// User session functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = Database::getInstance();
    return $db->fetchOne(
        "SELECT * FROM users WHERE id = ?",
        [$_SESSION['user_id']]
    );
}

// Product functions
function getFeaturedProducts($limit = 6) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT p.*, b.name as brand_name, c.name as category_name 
         FROM products p 
         LEFT JOIN brands b ON p.brand_id = b.id 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.featured = 1 AND p.status = 'active' 
         ORDER BY p.created_at DESC 
         LIMIT ?",
        [$limit]
    );
}

function getProductById($id) {
    $db = Database::getInstance();
    return $db->fetchOne(
        "SELECT p.*, b.name as brand_name, c.name as category_name 
         FROM products p 
         LEFT JOIN brands b ON p.brand_id = b.id 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.id = ? AND p.status = 'active'",
        [$id]
    );
}

function getProductImages($productId) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order",
        [$productId]
    );
}

function searchProducts($query, $category = null, $brand = null, $minPrice = null, $maxPrice = null, $limit = 12, $offset = 0): array
{
    $db = Database::getInstance();
    $params = [];
    $whereConditions = ["p.status = 'active'"];
    
    if ($query) {
        $whereConditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.model LIKE ?)";
        $searchTerm = "%{$query}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($category) {
        $whereConditions[] = "p.category_id = ?";
        $params[] = $category;
    }
    
    if ($brand) {
        $whereConditions[] = "p.brand_id = ?";
        $params[] = $brand;
    }
    
    if ($minPrice !== null) {
        $whereConditions[] = "(CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END) >= ?";
        $params[] = $minPrice;
    }
    
    if ($maxPrice !== null) {
        $whereConditions[] = "(CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END) <= ?";
        $params[] = $maxPrice;
    }
    
    $whereClause = implode(' AND ', $whereConditions);

    // Forcer entiers pour LIMIT/OFFSET et ne pas les passer comme placeholders si driver incompatible
    $limit = (int)$limit;
    $offset = (int)$offset;

    return $db->fetchAll(
        "SELECT p.*, b.name as brand_name, c.name as category_name 
         FROM products p 
         LEFT JOIN brands b ON p.brand_id = b.id 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE {$whereClause}
         ORDER BY p.name 
         LIMIT {$limit} OFFSET {$offset}",
        $params
    );
}

// Category and Brand functions
function getAllCategories() {
    $db = Database::getInstance();
    return $db->fetchAll("SELECT * FROM categories ORDER BY name");
}

function getAllBrands() {
    $db = Database::getInstance();
    return $db->fetchAll("SELECT * FROM brands ORDER BY name");
}

// Cart functions
function addToCart($productId, $quantity = 1, $userId = null) {
    $db = Database::getInstance();
    $sessionId = session_id();
    $productId = (int)$productId;
    $quantity = max(1, (int)$quantity);

    try {
        // Vérifier que le produit existe et est actif
        $product = $db->fetchOne("SELECT id, stock_quantity, status FROM products WHERE id = ?", [$productId]);
        if (!$product || $product['status'] !== 'active') {
            return ['success' => false, 'error' => 'Product not found or unavailable'];
        }

        // Vérifier le stock si la colonne existe
        if (isset($product['stock_quantity']) && is_numeric($product['stock_quantity']) && $product['stock_quantity'] < $quantity) {
            return ['success' => false, 'error' => 'Requested quantity exceeds available stock'];
        }

        // Transaction si supportée
        $useTx = method_exists($db, 'beginTransaction') && method_exists($db, 'commit') && method_exists($db, 'rollBack');
        if ($useTx) $db->beginTransaction();

        // Check if item already in cart
        if ($userId) {
            $existing = $db->fetchOne(
                "SELECT * FROM cart WHERE user_id = ? AND product_id = ?",
                [$userId, $productId]
            );
        } else {
            $existing = $db->fetchOne(
                "SELECT * FROM cart WHERE session_id = ? AND product_id = ?",
                [$sessionId, $productId]
            );
        }

        if ($existing) {
            // Update quantity
            $newQuantity = $existing['quantity'] + $quantity;
            if ($userId) {
                $db->update('cart',
                    ['quantity' => $newQuantity],
                    'user_id = ? AND product_id = ?',
                    [$userId, $productId]
                );
            } else {
                $db->update('cart',
                    ['quantity' => $newQuantity],
                    'session_id = ? AND product_id = ?',
                    [$sessionId, $productId]
                );
            }
        } else {
            // Add new item
            $cartData = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'added_at' => date('Y-m-d H:i:s')
            ];

            if ($userId) {
                $cartData['user_id'] = $userId;
            } else {
                $cartData['session_id'] = $sessionId;
            }

            $db->insert('cart', $cartData);
        }

        if ($useTx) $db->commit();
        return ['success' => true];
    } catch (Exception $e) {
        if (isset($useTx) && $useTx) {
            $db->rollBack();
        }
        // Log error si logError existe
        if (function_exists('logError')) {
            logError('cart_add_error', ['message' => $e->getMessage(), 'product_id' => $productId]);
        }
        return ['success' => false, 'error' => 'Erreur lors de l\'ajout au panier'];
    }
}

function getCartItems($userId = null) {
    $db = Database::getInstance();
    $sessionId = session_id();
    
    if ($userId) {
        return $db->fetchAll(
            "SELECT c.*, p.name, p.price, p.main_image, b.name as brand_name
             FROM cart c
             LEFT JOIN products p ON c.product_id = p.id
             LEFT JOIN brands b ON p.brand_id = b.id
             WHERE c.user_id = ?
             ORDER BY c.added_at DESC",
            [$userId]
        );
    } else {
        return $db->fetchAll(
            "SELECT c.*, p.name, p.price, p.main_image, b.name as brand_name
             FROM cart c
             LEFT JOIN products p ON c.product_id = p.id
             LEFT JOIN brands b ON p.brand_id = b.id
             WHERE c.session_id = ?
             ORDER BY c.added_at DESC",
            [$sessionId]
        );
    }
}

function getCartTotal($userId = null) {
    $items = getCartItems($userId);
    $total = 0;
    
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    return $total;
}

function getCartTotalForDisplay($userId = null) {
    $count = getCartItemCount($userId);
    if ($count == 0) {
        return ''; // Return empty string for empty cart
    }
    return formatPrice(getCartTotal($userId));
}

function getCartItemCount($userId = null) {
    $db = Database::getInstance();
    $sessionId = session_id();
    
    if ($userId) {
        return $db->fetchColumn(
            "SELECT SUM(quantity) FROM cart WHERE user_id = ?",
            [$userId]
        ) ?: 0;
    } else {
        return $db->fetchColumn(
            "SELECT SUM(quantity) FROM cart WHERE session_id = ?",
            [$sessionId]
        ) ?: 0;
    }
}

// Utility functions
function formatPrice($price) {
    // Force currency symbol to be a string dollar sign
    $currencySymbol = '$';
    return $currencySymbol . number_format($price, 2);
}

function formatWeight($weight) {
    return $weight . ' lbs';
}

function getProductImageUrl($imagePath) {
    if ($imagePath && !empty(trim($imagePath))) {
        $imagePath = trim($imagePath);
        // If it's already an absolute URL, return as-is
        if (preg_match('#^https?://#i', $imagePath)) {
            return $imagePath;
        }
        // If it's just a filename, assume it's in uploads/products/
        if (strpos($imagePath, '/') === false) {
            $webPath = 'uploads/products/' . $imagePath;
        } else {
            $webPath = ltrim($imagePath, '/');
        }
        
        $base = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
        if ($base === '') {
            return '/' . $webPath;
        }
        return $base . '/' . $webPath;
    }
    $base = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
    return $base . '/images/no-image.jpg';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function showMessage($message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function displayMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'info';
        
        echo "<div class='alert alert-{$type}'>{$message}</div>";
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

/**
 * Génère un numéro de commande unique, avec fallback si random_bytes indisponible
 */
function generateOrderNumber() {
    try {
        $rand = bin2hex(random_bytes(4));
    } catch (Exception $e) {
        // Fallback à uniqid si random_bytes indisponible
        $rand = strtoupper(uniqid());
    }
    return 'ORD-' . date('Y') . '-' . strtoupper($rand);
}

// Email function (basic implementation)
function sendEmail($to, $subject, $message, $headers = null) {
    if (!$headers) {
        $headers = "From: " . SITE_EMAIL . "\r\n";
        $headers .= "Reply-To: " . SITE_EMAIL . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    }
    
    return mail($to, $subject, $message, $headers);
}

// File upload function for images
function handleImageUpload($file, $folder = 'products'): array
{
    try {
        // Determine the correct upload directory path relative to project
        $projectRoot = realpath(__DIR__ . '/..');
        $uploadDir = $projectRoot . '/uploads/' . $folder . '/';

        // Ensure upload directory exists and is writable
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0775, true)) {
                return ['success' => false, 'error' => 'Cannot create upload directory'];
            }
        }
        
        // Set proper permissions for Ubuntu
        if (!is_writable($uploadDir)) {
            // Try to fix permissions only if we have permission to do so
            if (function_exists('chmod')) {
                $chmodResult = @chmod($uploadDir, 0775);
                // If chmod failed or directory still not writable, provide helpful error
                if (!$chmodResult || !is_writable($uploadDir)) {
                    return [
                        'success' => false, 
                        'error' => 'Upload directory is not writable. Please run: sudo chown -R www-data:www-data uploads/ && sudo chmod -R 775 uploads/'
                    ];
                }
            } else {
                return [
                    'success' => false, 
                    'error' => 'Upload directory is not writable. Please run: sudo chown -R www-data:www-data uploads/ && sudo chmod -R 775 uploads/'
                ];
            }
        }
        
        // Validate input
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'error' => 'Invalid file upload'];
        }
        
        $allowedTypes = ALLOWED_IMAGE_TYPES;
        $maxSize = MAX_FILE_SIZE;
        
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        $fileError = $file['error'];
        
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Check for upload errors
        switch ($fileError) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['success' => false, 'error' => 'No file was uploaded'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['success' => false, 'error' => 'File is too large'];
            default:
                return ['success' => false, 'error' => 'Unknown upload error'];
        }
        
        // Validate file size
        if ($fileSize > $maxSize) {
            return ['success' => false, 'error' => 'File too large. Maximum size: ' . round($maxSize / 1024 / 1024, 1) . 'MB'];
        }
        
        // Validate file extension
        if (!in_array($fileExt, $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes)];
        }
        
        // Enhanced image validation
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileTmp);
        finfo_close($finfo);
        
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($mimeType, $allowedMimes)) {
            return ['success' => false, 'error' => 'File is not a valid image'];
        }
        
        // Additional security: Check image dimensions and validate as actual image
        $imageInfo = getimagesize($fileTmp);
        if ($imageInfo === false) {
            return ['success' => false, 'error' => 'File is not a valid image format'];
        }
        
        // Security: Prevent extremely large dimensions
        if ($imageInfo[0] > 5000 || $imageInfo[1] > 5000) {
            return ['success' => false, 'error' => 'Image dimensions too large (max 5000x5000 pixels)'];
        }
        
        // Security: Check for embedded PHP code
        $fileContent = file_get_contents($fileTmp, false, null, 0, 1024);
        if (strpos($fileContent, '<?php') !== false || strpos($fileContent, '<%') !== false) {
            return ['success' => false, 'error' => 'Invalid file content detected'];
        }
        
        // Generate unique filename
        $newFileName = uniqid('img_') . '.' . $fileExt;
        $destination = $uploadDir . $newFileName;
        
        // Move uploaded file
        if (move_uploaded_file($fileTmp, $destination)) {
            // Set proper permissions for Ubuntu (use @ to suppress warnings)
            @chmod($destination, 0644);
            // Return web-accessible path relative to uploads/
            $webPath = 'uploads/' . $folder . '/' . $newFileName;
            return ['success' => true, 'filename' => $newFileName, 'path' => $webPath];
        }
        
        // If move failed, provide detailed error information
        $error = 'Failed to move uploaded file';
        if (!is_writable($uploadDir)) {
            $error .= ' - Upload directory not writable';
        }
        if (!is_readable($fileTmp)) {
            $error .= ' - Temporary file not readable';
        }
        
        return ['success' => false, 'error' => $error];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Upload error: ' . $e->getMessage()];
    }
}

// Simple crypto price fetcher with fallback and lightweight session cache
function getCryptoPrices(): array
{
    // Return cached prices for short time if available
    $cacheTtl = 300; // 5 minutes
    if (isset($_SESSION['crypto_prices']) && isset($_SESSION['crypto_prices_time']) && (time() - $_SESSION['crypto_prices_time']) < $cacheTtl) {
        return $_SESSION['crypto_prices'];
    }

    $default = [
        'bitcoin' => 50000.00,
        'usdt' => 1.00,
        'last_updated' => time(),
        'fallback' => true
    ];

    // Try CoinGecko simple price endpoint
    $url = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,tether&vs_currencies=usd';
    try {
        $opts = [
            'http' => [
                'method' => 'GET',
                'timeout' => 5
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            // Keep default fallback
            $_SESSION['crypto_prices'] = $default;
            $_SESSION['crypto_prices_time'] = time();
            return $default;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            $_SESSION['crypto_prices'] = $default;
            $_SESSION['crypto_prices_time'] = time();
            return $default;
        }

        $prices = [
            'bitcoin' => isset($data['bitcoin']['usd']) ? (float)$data['bitcoin']['usd'] : $default['bitcoin'],
            'usdt' => isset($data['tether']['usd']) ? (float)$data['tether']['usd'] : $default['usdt'],
            'last_updated' => time(),
            'fallback' => false
        ];

        // Cache into session
        $_SESSION['crypto_prices'] = $prices;
        $_SESSION['crypto_prices_time'] = time();

        return $prices;
    } catch (Exception $e) {
        // On any error return default
        $_SESSION['crypto_prices'] = $default;
        $_SESSION['crypto_prices_time'] = time();
        return $default;
    }
}

/**
 * Calculate amount of given crypto required for an USD amount.
 * Supported crypto keys: 'bitcoin', 'usdt'
 */
function calculateCryptoAmount(float $usdAmount, string $crypto = 'bitcoin'): float
{
    $prices = getCryptoPrices();
    $key = strtolower($crypto);
    $price = $prices[$key] ?? null;
    if (!$price || !is_numeric($price) || $price <= 0) {
        // Safe fallback to avoid division by zero
        $price = ($key === 'usdt') ? 1.0 : 50000.0;
    }
    return round($usdAmount / $price, ($key === 'bitcoin') ? 8 : 2);
}

/**
 * Create a Coinbase Commerce charge and persist minimal info to DB.
 * Requires COINBASE_COMMERCE_API_KEY constant (defined in includes/crypto_config.php).
 * Returns ['success'=>bool, 'hosted_url'=>string, 'code'=>string] or ['success'=>false,'error'=>string]
 */
function createCoinbaseCharge($orderId, $name, $description, $amount, $currency = 'USD') {
    if (!defined('COINBASE_COMMERCE_API_KEY') || empty(COINBASE_COMMERCE_API_KEY)) {
        return ['success' => false, 'error' => 'Coinbase API key not configured'];
    }

    $payload = [
        'name' => $name,
        'description' => $description,
        'local_price' => [
            'amount' => number_format((float)$amount, 2, '.', ''),
            'currency' => $currency
        ],
        'pricing_type' => 'fixed_price',
        'metadata' => [
            'order_id' => $orderId
        ]
    ];

    $ch = curl_init('https://api.commerce.coinbase.com/charges');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-CC-Api-Key: ' . COINBASE_COMMERCE_API_KEY,
        'X-CC-Version: 2018-03-22'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['success' => false, 'error' => 'Coinbase request failed: ' . $curlErr];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'error' => 'Invalid JSON from Coinbase'];
    }

    if ($httpCode >= 200 && $httpCode < 300 && isset($data['data'])) {
        $hostedUrl = $data['data']['hosted_url'] ?? '';
        $code = $data['data']['code'] ?? '';
        // Persist minimal charge info if DB available
        try {
            $db = Database::getInstance();
            $insert = [
                'order_id' => $orderId,
                'charge_code' => $code,
                'hosted_url' => $hostedUrl,
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'PENDING',
                'created_at' => date('Y-m-d H:i:s')
            ];
            $db->insert('coinbase_charges', $insert);
        } catch (Exception $e) {
            if (function_exists('logError')) {
                logError('coinbase_insert_failed', ['msg' => $e->getMessage(), 'order_id' => $orderId]);
            }
        }

        return ['success' => true, 'hosted_url' => $hostedUrl, 'code' => $code];
    }

    // Robust error extraction
    $errMsg = 'Coinbase API error';
    if (isset($data['error']) && is_array($data['error']) && isset($data['error']['message'])) {
        $errMsg = $data['error']['message'];
    } elseif (isset($data['errors']) && is_array($data['errors']) && isset($data['errors'][0]['message'])) {
        $errMsg = $data['errors'][0]['message'];
    } elseif (!empty($data)) {
        // fallback: stringify response for debugging
        $errMsg = 'Unexpected response from Coinbase';
    }

    return ['success' => false, 'error' => $errMsg];
}

// Additional currency formatting functions for better reliability
function getCurrencySymbol() {
    return '$'; // Hard-coded reliable currency symbol
}

function formatPriceSafe($price) {
    if (!is_numeric($price)) {
        return getCurrencySymbol() . '0.00';
    }
    return getCurrencySymbol() . number_format(floatval($price), 2);
}

function formatCurrency($amount, $symbol = null) {
    if ($symbol === null) {
        $symbol = getCurrencySymbol();
    }
    return $symbol . number_format(floatval($amount), 2);
}

?>
