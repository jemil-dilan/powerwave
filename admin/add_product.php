<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdmin();

$db = Database::getInstance();

// Handle form submission
if ($_POST && isset($_POST['add_product'])) {
    $name = sanitizeInput($_POST['name'] ?? '');
    $model = sanitizeInput($_POST['model'] ?? '');
    $brandId = (int)($_POST['brand_id'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $price = sanitizeInput($_POST['price'] ?? '');
    $salePrice = !empty($_POST['sale_price']) ? sanitizeInput($_POST['sale_price']) : null;
    $description = sanitizeInput($_POST['description'] ?? '');
    $specifications = sanitizeInput($_POST['specifications'] ?? '');
    $horsepower = (int)($_POST['horsepower'] ?? 0);
    $stroke = sanitizeInput($_POST['stroke'] ?? '4-stroke');
    $fuelType = sanitizeInput($_POST['fuel_type'] ?? 'gasoline');
    $shaftLength = sanitizeInput($_POST['shaft_length'] ?? 'long');
    $weight = !empty($_POST['weight']) ? (float)$_POST['weight'] : null;
    $displacement = !empty($_POST['displacement']) ? (float)$_POST['displacement'] : null;
    $cylinders = !empty($_POST['cylinders']) ? (int)$_POST['cylinders'] : null;
    $coolingSystem = sanitizeInput($_POST['cooling_system'] ?? 'water-cooled');
    $startingSystem = sanitizeInput($_POST['starting_system'] ?? 'manual');
    $stockQuantity = (int)($_POST['stock_quantity'] ?? 0);
    $minStockLevel = (int)($_POST['min_stock_level'] ?? 5);
    $sku = sanitizeInput($_POST['sku'] ?? '');
    $status = sanitizeInput($_POST['status'] ?? 'active');
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    $errors = [];
    
    // Validation
    if (empty($name)) $errors[] = 'Product name is required';
    if (empty($model)) $errors[] = 'Model is required';
    if ($brandId <= 0) $errors[] = 'Please select a brand';
    if ($categoryId <= 0) $errors[] = 'Please select a category';
    if (empty($price)) $errors[] = 'Price is required';
    if ($horsepower <= 0) $errors[] = 'Horsepower must be greater than 0';
    if (empty($sku)) $errors[] = 'SKU is required';
    
    // Check if SKU already exists
    if (!empty($sku)) {
        $existingSku = $db->fetchOne("SELECT id FROM products WHERE sku = ?", [$sku]);
        if ($existingSku) {
            $errors[] = 'SKU already exists';
        }
    }
    
    // Handle image upload
    $mainImage = null;
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleImageUpload($_FILES['main_image'], 'products');
        if ($uploadResult['success']) {
            $mainImage = $uploadResult['filename'];
        } else {
            $errors[] = 'Image upload failed: ' . $uploadResult['error'];
        }
    } elseif (isset($_FILES['main_image']) && $_FILES['main_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle upload errors (but not "no file" which is allowed)
        switch ($_FILES['main_image']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'Image file is too large';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = 'Image upload was interrupted';
                break;
            default:
                $errors[] = 'Image upload failed';
        }
    }
    
    if (!$errors) {
        try {
            $productData = [
                'name' => $name,
                'model' => $model,
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'price' => $price,
                'sale_price' => $salePrice,
                'description' => $description,
                'specifications' => $specifications,
                'horsepower' => $horsepower,
                'stroke' => $stroke,
                'fuel_type' => $fuelType,
                'shaft_length' => $shaftLength,
                'weight' => $weight,
                'displacement' => $displacement,
                'cylinders' => $cylinders,
                'cooling_system' => $coolingSystem,
                'starting_system' => $startingSystem,
                'stock_quantity' => $stockQuantity,
                'min_stock_level' => $minStockLevel,
                'sku' => $sku,
                'status' => $status,
                'featured' => $featured,
                'main_image' => $mainImage
            ];
            
            $productId = $db->insert('products', $productData);
            
            showMessage('Product added successfully!', 'success');
            redirect("edit_product.php?id=$productId");
            
        } catch (Exception $e) {
            $errors[] = 'Failed to add product: ' . $e->getMessage();
        }
    }
    
    if ($errors) {
        showMessage(implode('<br>', $errors), 'error');
    }
}

// Get brands and categories
$brands = getAllBrands();
$categories = getAllCategories();

$pageTitle = 'Add Product';
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
        .alert { padding: 12px 16px; margin-bottom: 16px; border-radius: 6px; border: 1px solid transparent; }
        .alert-success { background-color: #d1fae5; border-color: #a7f3d0; color: #065f46; }
        .alert-error { background-color: #fee2e2; border-color: #fca5a5; color: #dc2626; }
        .alert-info { background-color: #dbeafe; border-color: #93c5fd; color: #1e40af; }
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
            <h1>Add New Product</h1>
            <a href="products.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
        
        <?php displayMessage(); ?>
        
        <form method="POST" action="add_product.php" enctype="multipart/form-data">
            <!-- Basic Information -->
            <div class="form-section">
                <h3>Basic Information</h3>
                <div class="grid grid-2">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" class="input" required 
                               value="<?php echo isset($_POST['name']) ? sanitizeInput($_POST['name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Model *</label>
                        <input type="text" name="model" class="input" required 
                               value="<?php echo isset($_POST['model']) ? sanitizeInput($_POST['model']) : ''; ?>">
                    </div>
                </div>
                
                <div class="grid grid-2">
                    <div class="form-group">
                        <label>Brand *</label>
                        <select name="brand_id" class="input" required>
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>" 
                                        <?php echo isset($_POST['brand_id']) && $_POST['brand_id'] == $brand['id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitizeInput($brand['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" class="input" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo isset($_POST['category_id']) && $_POST['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitizeInput($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="input" rows="4"><?php echo isset($_POST['description']) ? sanitizeInput($_POST['description']) : ''; ?></textarea>
                </div>
            </div>
            
            <!-- Pricing -->
            <div class="form-section">
                <h3>Pricing</h3>
                <div class="grid grid-3">
                    <div class="form-group">
                        <label>Regular Price * ($)</label>
                        <input type="text" name="price" class="input" required placeholder="e.g. 2500.00, Call for price, Contact us" 
                               value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>">
                        <small style="color: #6b7280;">Enter price or text like 'Call for price'</small>
                    </div>
                    <div class="form-group">
                        <label>Sale Price ($)</label>
                        <input type="text" name="sale_price" class="input" placeholder="e.g. 2200.00, Special offer" 
                               value="<?php echo isset($_POST['sale_price']) ? $_POST['sale_price'] : ''; ?>">
                        <small style="color: #6b7280;">Leave empty if not on sale</small>
                    </div>
                    <div class="form-group">
                        <label>SKU *</label>
                        <input type="text" name="sku" class="input" required 
                               value="<?php echo isset($_POST['sku']) ? sanitizeInput($_POST['sku']) : ''; ?>">
                    </div>
                </div>
            </div>
            
            <!-- Technical Specifications -->
            <div class="form-section">
                <h3>Technical Specifications</h3>
                <div class="grid grid-3">
                    <div class="form-group">
                        <label>Horsepower *</label>
                        <input type="number" name="horsepower" class="input" min="1" required 
                               value="<?php echo isset($_POST['horsepower']) ? $_POST['horsepower'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Stroke Type *</label>
                        <select name="stroke" class="input" required>
                            <option value="2-stroke" <?php echo isset($_POST['stroke']) && $_POST['stroke'] === '2-stroke' ? 'selected' : ''; ?>>2-Stroke</option>
                            <option value="4-stroke" <?php echo !isset($_POST['stroke']) || $_POST['stroke'] === '4-stroke' ? 'selected' : ''; ?>>4-Stroke</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fuel Type</label>
                        <select name="fuel_type" class="input">
                            <option value="gasoline" <?php echo !isset($_POST['fuel_type']) || $_POST['fuel_type'] === 'gasoline' ? 'selected' : ''; ?>>Gasoline</option>
                            <option value="diesel" <?php echo isset($_POST['fuel_type']) && $_POST['fuel_type'] === 'diesel' ? 'selected' : ''; ?>>Diesel</option>
                            <option value="electric" <?php echo isset($_POST['fuel_type']) && $_POST['fuel_type'] === 'electric' ? 'selected' : ''; ?>>Electric</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-3">
                    <div class="form-group">
                        <label>Shaft Length</label>
                        <select name="shaft_length" class="input">
                            <option value="short" <?php echo isset($_POST['shaft_length']) && $_POST['shaft_length'] === 'short' ? 'selected' : ''; ?>>Short</option>
                            <option value="long" <?php echo !isset($_POST['shaft_length']) || $_POST['shaft_length'] === 'long' ? 'selected' : ''; ?>>Long</option>
                            <option value="extra-long" <?php echo isset($_POST['shaft_length']) && $_POST['shaft_length'] === 'extra-long' ? 'selected' : ''; ?>>Extra Long</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Weight (lbs)</label>
                        <input type="number" name="weight" class="input" step="0.1" min="0" 
                               value="<?php echo isset($_POST['weight']) ? $_POST['weight'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Displacement (cc)</label>
                        <input type="number" name="displacement" class="input" step="0.1" min="0" 
                               value="<?php echo isset($_POST['displacement']) ? $_POST['displacement'] : ''; ?>">
                    </div>
                </div>
                
                <div class="grid grid-3">
                    <div class="form-group">
                        <label>Cylinders</label>
                        <input type="number" name="cylinders" class="input" min="1" 
                               value="<?php echo isset($_POST['cylinders']) ? $_POST['cylinders'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Cooling System</label>
                        <select name="cooling_system" class="input">
                            <option value="water-cooled" <?php echo !isset($_POST['cooling_system']) || $_POST['cooling_system'] === 'water-cooled' ? 'selected' : ''; ?>>Water Cooled</option>
                            <option value="air-cooled" <?php echo isset($_POST['cooling_system']) && $_POST['cooling_system'] === 'air-cooled' ? 'selected' : ''; ?>>Air Cooled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Starting System</label>
                        <select name="starting_system" class="input">
                            <option value="manual" <?php echo !isset($_POST['starting_system']) || $_POST['starting_system'] === 'manual' ? 'selected' : ''; ?>>Manual</option>
                            <option value="electric" <?php echo isset($_POST['starting_system']) && $_POST['starting_system'] === 'electric' ? 'selected' : ''; ?>>Electric</option>
                            <option value="both" <?php echo isset($_POST['starting_system']) && $_POST['starting_system'] === 'both' ? 'selected' : ''; ?>>Both</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Detailed Specifications</label>
                    <textarea name="specifications" class="input" rows="4" 
                              placeholder="Additional technical specifications..."><?php echo isset($_POST['specifications']) ? sanitizeInput($_POST['specifications']) : ''; ?></textarea>
                </div>
            </div>
            
            <!-- Product Image -->
            <div class="form-section">
                <h3>Product Image</h3>
                <div class="form-group">
                    <label>Main Product Image</label>
                    <input type="file" name="main_image" class="input" accept="image/*">
                    <small style="color: #6b7280;">Supported formats: JPG, PNG, GIF. Maximum size: 5MB</small>
                </div>
            </div>
            
            <!-- Inventory & Status -->
            <div class="form-section">
                <h3>Inventory & Status</h3>
                <div class="grid grid-3">
                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock_quantity" class="input" min="0" required 
                               value="<?php echo isset($_POST['stock_quantity']) ? $_POST['stock_quantity'] : '0'; ?>">
                    </div>
                    <div class="form-group">
                        <label>Minimum Stock Level</label>
                        <input type="number" name="min_stock_level" class="input" min="0" 
                               value="<?php echo isset($_POST['min_stock_level']) ? $_POST['min_stock_level'] : '5'; ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="input">
                            <option value="active" <?php echo !isset($_POST['status']) || $_POST['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo isset($_POST['status']) && $_POST['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="discontinued" <?php echo isset($_POST['status']) && $_POST['status'] === 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="featured" value="1" <?php echo isset($_POST['featured']) ? 'checked' : ''; ?>>
                        Featured Product
                    </label>
                    <small style="color: #6b7280;">Featured products appear on the homepage</small>
                </div>
            </div>
            
            <div style="display: flex; gap: 16px;">
                <button type="submit" name="add_product" class="btn btn-primary">
                    <i class="fas fa-save"></i> Add Product
                </button>
                <a href="products.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
