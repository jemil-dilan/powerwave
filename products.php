<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$q = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : null;
$brand = isset($_GET['brand']) ? (int)$_GET['brand'] : null;
$minPrice = isset($_GET['min']) ? (float)$_GET['min'] : null;
$maxPrice = isset($_GET['max']) ? (float)$_GET['max'] : null;
$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$limit = PRODUCTS_PER_PAGE;
$offset = ($page - 1) * $limit;

$categories = getAllCategories();
$brands = getAllBrands();
$products = searchProducts($q, $category, $brand, $minPrice, $maxPrice, $limit, $offset);

// For simplicity, approximate total for pagination
$db = Database::getInstance();
$params = [];
$where = ["status = 'active'"];
if ($q) { $where[] = "(name LIKE ? OR description LIKE ? OR model LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($category) { $where[] = "category_id = ?"; $params[] = $category; }
if ($brand) { $where[] = "brand_id = ?"; $params[] = $brand; }
if ($minPrice !== null) { $where[] = "price >= ?"; $params[] = $minPrice; }
if ($maxPrice !== null) { $where[] = "price <= ?"; $params[] = $maxPrice; }
$whereSql = implode(' AND ', $where);
$total = (int)$db->fetchColumn("SELECT COUNT(*) FROM products WHERE $whereSql", $params);
$totalPages = max(1, (int)ceil($total / $limit));

$pageTitle = 'Products';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/responsive.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <header class="header">
    <div class="container">
      <div class="main-header">
        <div class="logo">
          <a href="index.php">
            <img src="logo1.png" alt="<?php echo SITE_NAME; ?>" style="height: 50px; width: auto;">
            <h1><?php echo SITE_NAME; ?></h1>
          </a>
        </div>
        <div class="search-bar">
          <form action="products.php" method="GET" class="search-form">
            <input type="text" name="q" placeholder="Search..." value="<?php echo $q; ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
          </form>
        </div>
        <div class="cart-info">
          <a href="cart.php" class="cart-link">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count"><?php echo getCartItemCount(isLoggedIn() ? $_SESSION['user_id'] : null); ?></span>
            <span class="cart-total"><?php echo formatPrice(getCartTotal(isLoggedIn() ? $_SESSION['user_id'] : null)); ?></span>
          </a>
        </div>
      </div>
      <nav class="navigation">
        <ul class="nav-menu">
          <li><a href="index.php">Home</a></li>
          <li><a class="active" href="products.php">Products</a></li>
          <li><a href="accessories.php">Accessories</a></li>
          <li><a href="brand.php">Our Brand</a></li>
          <li><a href="about.php">About</a></li>
          <li><a href="contact.php">Contact</a></li>
        </ul>
        <div class="mobile-menu-toggle"><i class="fas fa-bars"></i></div>
      </nav>
    </div>
  </header>

  <main class="container">
    <h2 class="section-title">Browse Outboard Motors</h2>
    <div class="filters">
      <form method="GET" action="products.php">
        <input type="hidden" name="q" value="<?php echo $q; ?>" />
        <select name="category">
          <option value="">All Categories</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?php echo $c['id']; ?>" <?php echo $category===$c['id']?'selected':''; ?>><?php echo sanitizeInput($c['name']); ?></option>
          <?php endforeach; ?>
        </select>
        <select name="brand">
          <option value="">All Brands</option>
          <?php foreach ($brands as $b): ?>
            <option value="<?php echo $b['id']; ?>" <?php echo $brand===$b['id']?'selected':''; ?>><?php echo sanitizeInput($b['name']); ?></option>
          <?php endforeach; ?>
        </select>
        <input type="number" name="min" step="0.01" placeholder="Min Price" value="<?php echo $minPrice!==null?$minPrice:''; ?>">
        <input type="number" name="max" step="0.01" placeholder="Max Price" value="<?php echo $maxPrice!==null?$maxPrice:''; ?>">
        <button class="btn btn-outline" type="submit">Filter</button>
        <a class="btn" href="products.php">Reset</a>
      </form>
    </div>

    <div class="products-grid">
      <?php if (!$products): ?>
        <p>No products found.</p>
      <?php else: ?>
        <?php foreach ($products as $p): ?>
          <div class="product-card">
            <div class="product-image">
              <a href="product.php?id=<?php echo $p['id']; ?>">
                <img src="<?php echo getProductImageUrl($p['main_image']); ?>" alt="<?php echo sanitizeInput($p['name']); ?>">
              </a>
              <?php if ($p['sale_price']): ?><span class="sale-badge">Sale</span><?php endif; ?>
              <div class="product-actions">
                <button class="btn-add-to-cart" data-product-id="<?php echo $p['id']; ?>"><i class="fas fa-cart-plus"></i></button>
                <a class="btn-quick-view" href="product.php?id=<?php echo $p['id']; ?>"><i class="fas fa-eye"></i></a>
              </div>
            </div>
            <div class="product-info">
              <h3><a href="product.php?id=<?php echo $p['id']; ?>"><?php echo sanitizeInput($p['name']); ?></a></h3>
              <p class="product-brand"><?php echo sanitizeInput($p['brand_name']); ?></p>
              <div class="product-specs"><span><?php echo (int)$p['horsepower']; ?>HP</span> <span><?php echo ucfirst($p['stroke']); ?></span></div>
              <div class="product-price">
                <?php if ($p['sale_price']): ?>
                  <span class="original-price"><?php echo formatPrice($p['price']); ?></span>
                  <span class="sale-price"><?php echo formatPrice($p['sale_price']); ?></span>
                <?php else: ?>
                  <span class="price"><?php echo formatPrice($p['price']); ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination" style="display:flex; gap:8px; justify-content:center; margin:16px 0;">
      <?php for ($i=1; $i<=$totalPages; $i++): ?>
        <?php $params = $_GET; $params['page']=$i; $url = 'products.php?'.http_build_query($params); ?>
        <a class="btn <?php echo $i===$page?'btn-primary':'btn-outline'; ?>" href="<?php echo $url; ?>"><?php echo $i; ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </main>

  <footer class="footer">
    <div class="container">
      <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        <div class="footer-links">
          <a href="privacy.php">Privacy Policy</a>
          <a href="terms.php">Terms of Service</a>
        </div>
      </div>
    </div>
  </footer>

  <script src="js/main.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Sélectionner tous les boutons d'ajout au panier
      const addToCartButtons = document.querySelectorAll('.btn-add-to-cart');

      addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          const productId = this.dataset.productId;
          const icon = this.querySelector('i');

          // Changer l'icône en indicateur de chargement
          icon.classList.remove('fa-cart-plus');
          icon.classList.add('fa-spinner', 'fa-spin');

          // Appel AJAX pour ajouter au panier
          fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
              product_id: productId,
              quantity: 1
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Mettre à jour le compteur et total du panier
              document.querySelector('.cart-count').textContent = data.cart_count;
              document.querySelector('.cart-total').textContent = data.cart_total;

              // Animation de succès
              icon.classList.remove('fa-spinner', 'fa-spin');
              icon.classList.add('fa-check');
              setTimeout(() => {
                icon.classList.remove('fa-check');
                icon.classList.add('fa-cart-plus');
              }, 1500);
            } else {
              throw new Error(data.message || 'Failed to add item to cart');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            // Restaurer l'icône en cas d'erreur
            icon.classList.remove('fa-spinner', 'fa-spin');
            icon.classList.add('fa-cart-plus');
            alert('Error adding item to cart: ' + error.message);
          });
        });
      });
    });
  </script>
</body>
</html>
