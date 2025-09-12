<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$q = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : null;
$brand = isset($_GET['brand']) ? (int)$_GET['brand'] : null;
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
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
if ($minPrice !== null) { $where[] = "(CASE WHEN sale_price > 0 THEN sale_price ELSE price END) >= ?"; $params[] = $minPrice; }
if ($maxPrice !== null) { $where[] = "(CASE WHEN sale_price > 0 THEN sale_price ELSE price END) <= ?"; $params[] = $maxPrice; }
$whereSql = implode(' AND ', $where);
$total = (int)$db->fetchColumn("SELECT COUNT(*) FROM products WHERE $whereSql", $params);
$totalPages = max(1, (int)ceil($total / $limit));

$pageTitle = 'Products';
$csrfToken = generateCSRFToken();
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
  <meta name="csrf-token" content="<?php echo $csrfToken; ?>">
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
    <div class="filters" style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin: 20px 0;">
      <form method="GET" action="products.php">
        <input type="hidden" name="q" value="<?php echo sanitizeInput($q); ?>" />
        <div class="grid grid-4" style="gap: 16px; margin-bottom: 16px;">
          <div class="form-group">
            <label style="display: block; margin-bottom: 4px; font-weight: 500;">Category</label>
            <select name="category" class="input">
              <option value="">All Categories</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?php echo $c['id']; ?>" <?php echo $category===$c['id']?'selected':''; ?>><?php echo sanitizeInput($c['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label style="display: block; margin-bottom: 4px; font-weight: 500;">Brand</label>
            <select name="brand" class="input">
              <option value="">All Brands</option>
              <?php foreach ($brands as $b): ?>
                <option value="<?php echo $b['id']; ?>" <?php echo $brand===$b['id']?'selected':''; ?>><?php echo sanitizeInput($b['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label style="display: block; margin-bottom: 4px; font-weight: 500;">Min Price ($)</label>
            <input type="number" name="min_price" class="input" step="0.01" placeholder="0.00" value="<?php echo $minPrice!==null?$minPrice:''; ?>" min="0">
          </div>
          <div class="form-group">
            <label style="display: block; margin-bottom: 4px; font-weight: 500;">Max Price ($)</label>
            <input type="number" name="max_price" class="input" step="0.01" placeholder="999999.99" value="<?php echo $maxPrice!==null?$maxPrice:''; ?>" min="0">
          </div>
        </div>
        <div style="display: flex; gap: 8px;">
          <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Apply Filters</button>
          <a class="btn btn-outline" href="products.php"><i class="fas fa-times"></i> Clear All</a>
        </div>
      </form>
    </div>

    <?php if ($q || $category || $brand || $minPrice !== null || $maxPrice !== null): ?>
    <div style="background: #f8fafc; padding: 12px; border-radius: 8px; margin: 16px 0; border: 1px solid #e2e8f0;">
      <div style="display: flex; align-items: center; gap: 8px; font-size: 14px;">
        <i class="fas fa-filter" style="color: #0ea5e9;"></i>
        <span style="font-weight: 500;">Active filters:</span>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
          <?php if ($q): ?>
            <span class="filter-tag" style="background: #0ea5e9; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px;">
              Search: "<?php echo sanitizeInput($q); ?>"
            </span>
          <?php endif; ?>
          <?php if ($category): ?>
            <?php $catName = ''; foreach($categories as $c) { if($c['id'] == $category) $catName = $c['name']; } ?>
            <span class="filter-tag" style="background: #059669; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px;">
              Category: <?php echo sanitizeInput($catName); ?>
            </span>
          <?php endif; ?>
          <?php if ($brand): ?>
            <?php $brandName = ''; foreach($brands as $b) { if($b['id'] == $brand) $brandName = $b['name']; } ?>
            <span class="filter-tag" style="background: #7c3aed; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px;">
              Brand: <?php echo sanitizeInput($brandName); ?>
            </span>
          <?php endif; ?>
          <?php if ($minPrice !== null): ?>
            <span class="filter-tag" style="background: #f59e0b; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px;">
              Min: <?php echo formatPrice($minPrice); ?>
            </span>
          <?php endif; ?>
          <?php if ($maxPrice !== null): ?>
            <span class="filter-tag" style="background: #ef4444; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px;">
              Max: <?php echo formatPrice($maxPrice); ?>
            </span>
          <?php endif; ?>
        </div>
        <span style="color: #6b7280; margin-left: auto;"><?php echo count($products); ?> of <?php echo $total; ?> products</span>
      </div>
    </div>
    <?php endif; ?>

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
    // Enhanced filter functionality
    document.addEventListener('DOMContentLoaded', function() {
      const filterForm = document.querySelector('form[action="products.php"]');
      const categorySelect = document.querySelector('select[name="category"]');
      const brandSelect = document.querySelector('select[name="brand"]');
      const minPriceInput = document.querySelector('input[name="min_price"]');
      const maxPriceInput = document.querySelector('input[name="max_price"]');
      const filterBtn = document.querySelector('button[type="submit"]');
      
      // Add loading state to filter button
      if (filterForm) {
        filterForm.addEventListener('submit', function() {
          if (filterBtn) {
            filterBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Filtering...';
            filterBtn.disabled = true;
          }
        });
      }
      
      // Price input validation
      const priceInputs = [minPriceInput, maxPriceInput].filter(Boolean);
      priceInputs.forEach(input => {
        input.addEventListener('blur', function() {
          let value = parseFloat(this.value);
          if (this.value && value < 0) {
            this.value = '0';
          }
          
          // Auto-format to 2 decimal places if value exists
          if (this.value && !isNaN(value)) {
            this.value = value.toFixed(2);
          }
        });
      });
      
      // Price range validation
      if (minPriceInput && maxPriceInput) {
        function validatePriceRange() {
          const minVal = parseFloat(minPriceInput.value);
          const maxVal = parseFloat(maxPriceInput.value);
          
          if (minVal && maxVal && minVal > maxVal) {
            maxPriceInput.style.borderColor = '#ef4444';
            maxPriceInput.title = 'Max price should be greater than min price';
          } else {
            maxPriceInput.style.borderColor = '';
            maxPriceInput.title = '';
          }
        }
        
        minPriceInput.addEventListener('blur', validatePriceRange);
        maxPriceInput.addEventListener('blur', validatePriceRange);
      }
      
      // Add keyboard shortcuts
      document.addEventListener('keydown', function(e) {
        // Ctrl+F or Cmd+F to focus filter form
        if ((e.ctrlKey || e.metaKey) && e.key === 'f' && !e.defaultPrevented) {
          if (categorySelect) {
            e.preventDefault();
            categorySelect.focus();
          }
        }
      });
      
      // Show filter count in real-time (optional enhancement)
      const activeFilters = document.querySelectorAll('.filter-tag');
      if (activeFilters.length > 0) {
        console.log(`Active filters: ${activeFilters.length}`);
      }
    });
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
