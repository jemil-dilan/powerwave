<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$cartId = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;

if ($cartId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart ID']);
    exit;
}

try {
    $db = Database::getInstance();
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    
    // Verify cart item belongs to current user/session
    if ($userId) {
        $cartItem = $db->fetchOne(
            "SELECT * FROM cart WHERE id = ? AND user_id = ?",
            [$cartId, $userId]
        );
    } else {
        $sessionId = session_id();
        $cartItem = $db->fetchOne(
            "SELECT * FROM cart WHERE id = ? AND session_id = ?",
            [$cartId, $sessionId]
        );
    }
    
    if (!$cartItem) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit;
    }
    
    // Remove item
    $db->delete('cart', 'id = ?', [$cartId]);
    
    // Get updated cart info
    $cartCount = (int)getCartItemCount($userId);
    $cartTotal = (float)getCartTotal($userId);
    
    echo json_encode([
        'success' => true,
        'cart_count' => $cartCount,
        'cart_total' => $cartTotal,
        'cart_total_formatted' => formatPrice($cartTotal)
    ]);
    
} catch (Exception $e) {
    error_log("Cart item removal failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
}
?>
