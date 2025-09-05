<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

// Basic validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid request token.']);
    exit;
}

$cartId = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

if ($cartId <= 0 || $quantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

try {
    $db = Database::getInstance();
    $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
    $sessionId = session_id();

    // First, verify the cart item belongs to the current user/session to prevent unauthorized modification
    $owner_check_sql = $userId 
        ? "SELECT * FROM cart WHERE id = ? AND user_id = ?"
        : "SELECT * FROM cart WHERE id = ? AND session_id = ?";
    $owner_check_params = $userId ? [$cartId, $userId] : [$cartId, $sessionId];
    
    $cartItem = $db->fetchOne($owner_check_sql, $owner_check_params);

    if (!$cartItem) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found or permission denied.']);
        exit;
    }

    // If quantity is 0, delete the item. Otherwise, update it.
    if ($quantity === 0) {
        $db->delete('cart', 'id = ?', [$cartId]);
    } else {
        $db->update('cart', ['quantity' => $quantity], 'id = ?', [$cartId]);
    }

    // Recalculate totals for the entire cart
    $newCartCount = getCartItemCount($userId);
    $newCartTotal = getCartTotal($userId);

    echo json_encode([
        'success' => true,
        'cart_count' => $newCartCount,
        'cart_total_formatted' => formatPrice($newCartTotal),
        'new_quantity' => $quantity
    ]);

} catch (Exception $e) {
    error_log("Cart update failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating the cart.']);
}
?>
