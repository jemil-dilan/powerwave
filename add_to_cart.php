<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Rate limiting check - prevent spam requests
if (!isset($_SESSION['last_cart_action'])) {
    $_SESSION['last_cart_action'] = 0;
}
if (time() - $_SESSION['last_cart_action'] < 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please wait before adding more items']);
    exit;
}

// Handle both AJAX and traditional form submissions
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
          (!empty($_SERVER['CONTENT_TYPE']) && 
          strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

if ($isAjax) {
    header('Content-Type: application/json');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    } else {
        showMessage('Invalid request method', 'error');
        redirect($_SERVER['HTTP_REFERER'] ?? 'products.php');
    }
    exit;
}

// CSRF Protection for traditional form submissions
    // For AJAX, we expect the token in a header. For forms, in the POST body.
    if ($isAjax) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    } else {
        $token = $_POST['csrf_token'] ?? '';
    }
    
    if (!validateCSRFToken($token)) {
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        } else {
            showMessage('Invalid security token. Please try again.', 'error');
            redirect($_SERVER['HTTP_REFERER'] ?? 'products.php');
        }
        exit;
    }

// Input validation and sanitization
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, min(99, (int)$_POST['quantity'])) : 1;

try {
    // Enhanced validation
    if ($productId <= 0) {
        throw new Exception('Invalid product ID');
    }
    
    if ($quantity <= 0 || $quantity > 99) {
        throw new Exception('Invalid quantity. Please select between 1 and 99 items');
    }
    
    $db = Database::getInstance();
    
    // Get product with stock information
    $product = $db->fetchOne(
        "SELECT p.*, b.name as brand_name, c.name as category_name 
         FROM products p 
         JOIN brands b ON p.brand_id = b.id 
         JOIN categories c ON p.category_id = c.id 
         WHERE p.id = ? AND p.status = 'active'",
        [$productId]
    );
    
    if (!$product) {
        throw new Exception('Product not found or no longer available');
    }
    
    // Stock validation
    if ($product['stock_quantity'] !== null && $product['stock_quantity'] < $quantity) {
        if ($product['stock_quantity'] <= 0) {
            throw new Exception('Sorry, this product is currently out of stock');
        } else {
            throw new Exception("Only {$product['stock_quantity']} items available in stock");
        }
    }
    
    // Check if item already in cart and validate total quantity
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    $sessionId = session_id();
    
    $existingCartItem = null;
    if ($userId) {
        $existingCartItem = $db->fetchOne(
            "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?",
            [$userId, $productId]
        );
    } else {
        $existingCartItem = $db->fetchOne(
            "SELECT quantity FROM cart WHERE session_id = ? AND product_id = ?",
            [$sessionId, $productId]
        );
    }
    
    $totalQuantityInCart = $existingCartItem ? $existingCartItem['quantity'] + $quantity : $quantity;
    
    if ($product['stock_quantity'] !== null && $product['stock_quantity'] < $totalQuantityInCart) {
        $available = max(0, $product['stock_quantity'] - ($existingCartItem['quantity'] ?? 0));
        if ($available <= 0) {
            throw new Exception('You already have the maximum available quantity in your cart');
        } else {
            throw new Exception("You can only add {$available} more of this item (stock limit)");
        }
    }
    
    // Add to cart
    $success = addToCart($productId, $quantity, $userId);
    
    if (!$success) {
        throw new Exception('Failed to add item to cart. Please try again.');
    }
    
    // Update rate limiting
    $_SESSION['last_cart_action'] = time();
    
    // Get updated cart information
    $cartCount = getCartItemCount($userId);
    $cartTotal = getCartTotal($userId);
    $cartTotalFormatted = formatPrice($cartTotal);
    
    // Calculate shipping and tax for display
    $shipping = SHIPPING_RATE;
    $tax = round($cartTotal * TAX_RATE, 2);
    $grandTotal = $cartTotal + $shipping + $tax;
    
    $response = [
        'success' => true,
        'message' => $quantity == 1 
            ? "Added '{$product['name']}' to your cart" 
            : "Added {$quantity} x '{$product['name']}' to your cart",
        'cart_count' => $cartCount,
        'cart_total' => $cartTotal,
        'cart_total_formatted' => $cartTotalFormatted,
        'cart_subtotal' => $cartTotal,
        'cart_subtotal_formatted' => $cartTotalFormatted,
        'cart_shipping' => $shipping,
        'cart_shipping_formatted' => formatPrice($shipping),
        'cart_tax' => $tax,
        'cart_tax_formatted' => formatPrice($tax),
        'cart_grand_total' => $grandTotal,
        'cart_grand_total_formatted' => formatPrice($grandTotal),
        'product' => [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['sale_price'] ?: $product['price'],
            'price_formatted' => formatPrice($product['sale_price'] ?: $product['price']),
            'quantity_added' => $quantity
        ]
    ];
    
    if ($isAjax) {
        echo json_encode($response);
    } else {
        showMessage($response['message'], 'success');
        redirect($_SERVER['HTTP_REFERER'] ?? 'products.php');
    }
    
} catch (Exception $e) {
    error_log('Add to cart error: ' . $e->getMessage());
    
    $errorResponse = [
        'success' => false, 
        'message' => $e->getMessage()
    ];
    
    if ($isAjax) {
        echo json_encode($errorResponse);
    } else {
        showMessage($errorResponse['message'], 'error');
        redirect($_SERVER['HTTP_REFERER'] ?? 'products.php');
    }
}