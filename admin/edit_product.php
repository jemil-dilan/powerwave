<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check admin access
if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$productId) {
    $_SESSION['message'] = 'No product ID specified';
    $_SESSION['message_type'] = 'error';
    header('Location: products.php');
    exit;
}

// Get existing product data
try {
    $product = $db->fetchOne(
        "SELECT * FROM products WHERE id = ?",
        [$productId]
    );
    
    if (!$product) {
        $_SESSION['message'] = 'Product not found';
        $_SESSION['message_type'] = 'error';
        header('Location: products.php');
        exit;
    }
} catch (Exception $e) {
    die("Error fetching product: " . $e->getMessage());
}

// Get categories and brands for dropdowns
try {
    $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");
    $brands = $db->fetchAll("SELECT * FROM brands ORDER BY name");
} catch (Exception $e) {
    die("Error fetching categories/brands: " . $e->getMessage());
}

// Handle form submission
if ($_POST && isset($_POST['update_product'])) {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $categoryId = (int)$_POST['category_id'];
    $brandId = (int)$_POST['brand_id'];
    $model = sanitizeInput($_POST['model']);
    $sku = sanitizeInput($_POST['sku']);
    $price = sanitizeInput($_POST['price']);
    $salePrice = !empty($_POST['sale_price']) ? sanitizeInput($_POST['sale_price']) : null;
    $stockQuantity = (int)$_POST['stock_quantity'];
    $minStockLevel = (int)$_POST['min_stock_level'];
    $horsepower = (int)$_POST['horsepower'];
    $stroke = sanitizeInput($_POST['stroke']);
    $fuelType = sanitizeInput($_POST['fuel_type']);
    $weight = (float)$_POST['weight'];
    $dimensions = sanitizeInput($_POST['dimensions']);
    $features = sanitizeInput($_POST['features']);
    $specifications = sanitizeInput($_POST['specifications']);
    $status = sanitizeInput($_POST['status']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    $errors = [];
    
    // Validation
    if (empty($name)) $errors[] = "Product name is required";
    if (empty($description)) $errors[] = "Description is required";
    if (!$categoryId) $errors[] = "Category is required";
    if (!$brandId) $errors[] = "Brand is required";
    if (empty($model)) $errors[] = "Model is required";
    if (empty($sku)) $errors[] = "SKU is required";
    if (empty($price)) $errors[] = "Price is required";
    if ($stockQuantity < 0) $errors[] = "Stock quantity cannot be negative";
    if ($minStockLevel < 0) $errors[] = "Minimum stock level cannot be negative";
    if ($horsepower <= 0) $errors[] = "Horsepower must be greater than 0";
    
    // Check if SKU is unique (excluding current product)
    $existingSku = $db->fetchOne("SELECT id FROM products WHERE sku = ? AND id != ?", [$sku, $productId]);
    if ($existingSku) {
        $errors[] = "SKU already exists";
    }
    
    if (empty($errors)) {
        try {
            $updateData = [
                'name' => $name,
                'description' => $description,
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'model' => $model,
                'sku' => $sku,
                'price' => $price,
                'sale_price' => $salePrice,
                'stock_quantity' => $stockQuantity,
                'min_stock_level' => $minStockLevel,
                'horsepower' => $horsepower,
                'stroke' => $stroke,
                'fuel_type' => $fuelType,
                'weight' => $weight,
                'dimensions' => $dimensions,
                'features' => $features,
                'specifications' => $specifications,
                'status' => $status,
                'featured' => $featured,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Handle image upload if provided
            if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = handleImageUpload($_FILES['main_image'], 'products');
                    if ($uploadResult['success']) {
                        // Delete old image if it exists
                        if ($product['main_image'] && file_exists("../uploads/products/" . $product['main_image'])) {
                            unlink("../uploads/products/" . $product['main_image']);
                        }
                        $updateData['main_image'] = $uploadResult['filename'];
                        showMessage('Image uploaded successfully: ' . $uploadResult['filename'], 'success');
                    } else {
                        $errors[] = 'Image upload failed: ' . $uploadResult['error'];
                    }
                } else {
                    // Handle different upload errors
                    switch ($_FILES['main_image']['error']) {
                        case UPLOAD_ERR_INI_SIZE:
                            $errors[] = 'Image too large (exceeds PHP ini setting)';
                            break;
                        case UPLOAD_ERR_FORM_SIZE:
                            $errors[] = 'Image too large (exceeds form limit)';
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $errors[] = 'Image upload was interrupted';
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $errors[] = 'Missing temporary folder for upload';
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $errors[] = 'Failed to write image to disk';
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $errors[] = 'Upload stopped by PHP extension';
                            break;
                        default:
                            $errors[] = 'Unknown upload error: ' . $_FILES['main_image']['error'];
                    }
                }
            }
            
            if (empty($errors)) {
                $db->update('products', $updateData, 'id = ?', [$productId]);
                showMessage('Product updated successfully!', 'success');
                redirect('products.php');
            }
        } catch (Exception $e) {
            $errors[] = 'Error updating product: ' . $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            showMessage($error, 'error');
        }
    }
}

$pageTitle = 'Edit Product';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-sidebar { width: 250px; background: #1f2937; min-height: 100vh; position: fixed; left: 0; top: 0; }
        .admin-sidebar h2 { color: white; padding: 20px; margin: 0; border-bottom: 1px solid #374151; }
        .admin-nav { list-style: none; padding: 0; margin: 0; }
        .admin-nav li a { display: block; color: #d1d5db; padding: 12px 20px; text-decoration: none; }
        .admin-nav li a:hover, .admin-nav li a.active { background: #374151; color: white; }
        .admin-content { margin-left: 250px; padding: 20px; }
        .form-section { background: white; padding: 24px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e5e7eb; }
        .form-section h3 { margin-top: 0; margin-bottom: 16px; color: #1f2937; }
        .current-image { max-width: 200px; height: auto; border-radius: 8px; margin-bottom: 10px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
        .full-width { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 6px; font-weight: 600; color: #374151; }
        .input, .select, .textarea { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        .textarea { min-height: 100px; resize: vertical; font-family: inherit; }
        .checkbox-wrapper { display: flex; align-items: center; gap: 8px; }
        .checkbox { width: auto; }
    </style>
</head>
<body>
    <div class="admin-sidebar">
        <h2><i class="fas fa-anchor"></i> Admin</h2>
        <ul class="admin-nav">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="products.php" class="active"><i class="fas fa-cog"></i> Products</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="../index.php"><i class="fas fa-external-link-alt"></i> View Site</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>Edit Product: <?php echo sanitizeInput($product['name']); ?></h1>
            <a href="products.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
        
        <?php displayMessage(); ?>
        
        <form method="POST" enctype="multipart/form-data">
            <!-- Basic Information -->
            <div class="form-section">
                <h3>Basic Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" class="input" value="<?php echo sanitizeInput($product['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="sku">SKU *</label>
                        <input type="text" id="sku" name="sku" class="input" value="<?php echo sanitizeInput($product['sku']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" class="textarea" required><?php echo sanitizeInput($product['description']); ?></textarea>
                </div>
            </div>
            
            <!-- Categorization -->
            <div class="form-section">
                <h3>Categorization</h3>
                <div class="form-grid-3">
                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" class="select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitizeInput($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="brand_id">Brand *</label>
                        <select id="brand_id" name="brand_id" class="select" required>
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>" <?php echo $product['brand_id'] == $brand['id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitizeInput($brand['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="model">Model *</label>
                        <input type="text" id="model" name="model" class="input" value="<?php echo sanitizeInput($product['model']); ?>" required>
                    </div>
                </div>
            </div>
            
            <!-- Pricing & Inventory -->
            <div class="form-section">
                <h3>Pricing & Inventory</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="price">Price ($) *</label>
                        <input type="text" id="price" name="price" class="input" value="<?php echo sanitizeInput($product['price']); ?>" required placeholder="e.g. 2500.00, Call for price, Contact us">
                        <small style="color: #6b7280;">Enter price or text like 'Call for price'</small>
                    </div>
                    <div class="form-group">
                        <label for="sale_price">Sale Price ($)</label>
                        <input type="text" id="sale_price" name="sale_price" class="input" value="<?php echo sanitizeInput($product['sale_price']); ?>" placeholder="e.g. 2200.00, Special offer">
                        <small style="color: #6b7280;">Leave empty if not on sale</small>
                    </div>
                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity *</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" class="input" min="0" value="<?php echo $product['stock_quantity']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="min_stock_level">Minimum Stock Level *</label>
                        <input type="number" id="min_stock_level" name="min_stock_level" class="input" min="0" value="<?php echo $product['min_stock_level']; ?>" required>
                    </div>
                </div>
            </div>
            
            <!-- Technical Specifications -->
            <div class="form-section">
                <h3>Technical Specifications</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="horsepower">Horsepower *</label>
                        <input type="number" id="horsepower" name="horsepower" class="input" min="1" value="<?php echo $product['horsepower']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="stroke">Stroke Type *</label>
                        <select id="stroke" name="stroke" class="select" required>
                            <option value="">Select Stroke</option>
                            <option value="2-stroke" <?php echo $product['stroke'] === '2-stroke' ? 'selected' : ''; ?>>2-Stroke</option>
                            <option value="4-stroke" <?php echo $product['stroke'] === '4-stroke' ? 'selected' : ''; ?>>4-Stroke</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fuel_type">Fuel Type *</label>
                        <select id="fuel_type" name="fuel_type" class="select" required>
                            <option value="">Select Fuel Type</option>
                            <option value="gasoline" <?php echo $product['fuel_type'] === 'gasoline' ? 'selected' : ''; ?>>Gasoline</option>
                            <option value="diesel" <?php echo $product['fuel_type'] === 'diesel' ? 'selected' : ''; ?>>Diesel</option>
                            <option value="electric" <?php echo $product['fuel_type'] === 'electric' ? 'selected' : ''; ?>>Electric</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="weight">Weight (lbs)</label>
                        <input type="number" id="weight" name="weight" class="input" step="0.1" min="0" value="<?php echo $product['weight']; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="dimensions">Dimensions (L x W x H)</label>
                    <input type="text" id="dimensions" name="dimensions" class="input" value="<?php echo sanitizeInput($product['dimensions'] ?? ''); ?>" placeholder="e.g., 48 x 20 x 45 inches">
                </div>
            </div>
            
            <!-- Additional Details -->
            <div class="form-section">
                <h3>Additional Details</h3>
                <div class="form-group">
                    <label for="features">Features</label>
                    <textarea id="features" name="features" class="textarea" placeholder="List key features, one per line"><?php echo sanitizeInput($product['features'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="specifications">Additional Specifications</label>
                    <textarea id="specifications" name="specifications" class="textarea" placeholder="Any additional technical specifications"><?php echo sanitizeInput($product['specifications']); ?></textarea>
                </div>
            </div>
            
            <!-- Product Image -->
            <div class="form-section">
                <h3>Product Image</h3>
                <?php if ($product['main_image']): ?>
                    <div class="form-group">
                        <label>Current Image:</label>
                        <div>
                            <img src="<?php echo getProductImageUrl($product['main_image']); ?>" alt="Current Product Image" class="current-image">
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="main_image">Upload New Image (optional)</label>
                    <input type="file" id="main_image" name="main_image" class="input" accept="image/*">
                    <small style="color: #6b7280;">Leave empty to keep current image. Supported formats: JPG, PNG, GIF. Max size: 5MB</small>
                </div>
            </div>
            
            <!-- Status & Visibility -->
            <div class="form-section">
                <h3>Status & Visibility</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" class="select" required>
                            <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="discontinued" <?php echo $product['status'] === 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="featured" name="featured" class="checkbox" <?php echo $product['featured'] ? 'checked' : ''; ?>>
                            <label for="featured">Featured Product</label>
                        </div>
                        <small style="color: #6b7280;">Featured products appear on the homepage</small>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 16px; justify-content: flex-end;">
                <a href="products.php" class="btn btn-outline">Cancel</a>
                <button type="submit" name="update_product" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Product
                </button>
            </div>
        </form>
    </div>
</body>
</html>
