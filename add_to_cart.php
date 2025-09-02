<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request']);
  exit;
}

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

try {
  if ($productId <= 0) { throw new Exception('Invalid product'); }
  $product = getProductById($productId);
  if (!$product) { throw new Exception('Product not found'); }
  if ($product['status'] !== 'active') { throw new Exception('Product unavailable'); }

  addToCart($productId, $quantity, isLoggedIn() ? $_SESSION['user_id'] : null);

  $count = (int)getCartItemCount(isLoggedIn() ? $_SESSION['user_id'] : null);
  $total = (float)getCartTotal(isLoggedIn() ? $_SESSION['user_id'] : null);

  echo json_encode([
    'success' => true,
    'cart_count' => $count,
    'cart_total' => $total,
    'cart_total_formatted' => formatPrice($total)
  ]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

