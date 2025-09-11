<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

echo "=== Upload Directory Diagnostic & Fix Tool ===\n\n";

function checkAndFixPermissions() {
    $directories = [
        'uploads/',
        'uploads/products/',
        'uploads/brands/',
        'uploads/categories/'
    ];
    
    foreach ($directories as $dir) {
        echo "Checking directory: $dir\n";
        
        // Check if directory exists
        if (!is_dir($dir)) {
            echo "  - Directory doesn't exist, creating...\n";
            if (mkdir($dir, 0777, true)) {
                echo "  - Successfully created directory\n";
            } else {
                echo "  - FAILED to create directory\n";
                continue;
            }
        } else {
            echo "  - Directory exists\n";
        }
        
        // Check permissions
        if (!is_writable($dir)) {
            echo "  - Directory is not writable, attempting to fix...\n";
            if (chmod($dir, 0777)) {
                echo "  - Successfully set permissions to 0777\n";
            } else {
                echo "  - FAILED to set permissions\n";
            }
        } else {
            echo "  - Directory is writable\n";
        }
        
        // Test write capability
        $testFile = $dir . 'test_write_' . uniqid() . '.txt';
        if (file_put_contents($testFile, 'test')) {
            echo "  - Write test PASSED\n";
            unlink($testFile);
        } else {
            echo "  - Write test FAILED\n";
        }
        
        echo "\n";
    }
}

function testImageUpload() {
    echo "=== Testing Image Upload Function ===\n\n";
    
    // Create a test image file
    $testImagePath = 'test_image.png';
    $image = imagecreate(100, 100);
    $backgroundColor = imagecolorallocate($image, 255, 255, 255);
    $textColor = imagecolorallocate($image, 0, 0, 0);
    imagestring($image, 5, 30, 40, 'TEST', $textColor);
    imagepng($image, $testImagePath);
    imagedestroy($image);
    
    // Simulate $_FILES array
    $simulatedFile = [
        'name' => 'test_image.png',
        'type' => 'image/png',
        'tmp_name' => $testImagePath,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($testImagePath)
    ];
    
    echo "Testing handleImageUpload function...\n";
    $result = handleImageUpload($simulatedFile, 'products');
    
    if ($result['success']) {
        echo "SUCCESS: Image upload function works correctly\n";
        echo "Uploaded file: " . $result['filename'] . "\n";
        echo "Path: " . $result['path'] . "\n";
        
        // Clean up uploaded test file
        $uploadedFile = 'uploads/products/' . $result['filename'];
        if (file_exists($uploadedFile)) {
            unlink($uploadedFile);
            echo "Cleaned up uploaded test file\n";
        }
    } else {
        echo "FAILED: " . $result['error'] . "\n";
        
        // Debug information
        echo "\nDebug Information:\n";
        echo "Current working directory: " . getcwd() . "\n";
        echo "Checking from admin folder: " . (basename(getcwd()) === 'admin' ? 'YES' : 'NO') . "\n";
        
        $uploadDir = (basename(getcwd()) === 'admin') ? '../uploads/products/' : 'uploads/products/';
        echo "Upload directory path: " . $uploadDir . "\n";
        echo "Upload directory exists: " . (is_dir($uploadDir) ? 'YES' : 'NO') . "\n";
        echo "Upload directory writable: " . (is_writable($uploadDir) ? 'YES' : 'NO') . "\n";
        
        // Try alternative paths
        echo "\nTrying alternative paths:\n";
        $altPaths = [
            '../uploads/products/',
            'uploads/products/',
            './uploads/products/',
            __DIR__ . '/uploads/products/',
            realpath('.') . '/uploads/products/',
            realpath('..') . '/uploads/products/'
        ];
        
        foreach ($altPaths as $path) {
            $realPath = realpath($path);
            echo "Path: $path -> Real: " . ($realPath ? $realPath : 'NOT FOUND') . 
                 " | Exists: " . (is_dir($path) ? 'YES' : 'NO') . 
                 " | Writable: " . (is_writable($path) ? 'YES' : 'NO') . "\n";
        }
    }
    
    // Clean up test file
    if (file_exists($testImagePath)) {
        unlink($testImagePath);
    }
    
    echo "\n";
}

function testDatabaseConnection() {
    echo "=== Testing Database Connection ===\n\n";
    
    try {
        $db = Database::getInstance();
        echo "Database connection: SUCCESS\n";
        
        // Test a simple query
        $result = $db->fetchColumn("SELECT COUNT(*) FROM products");
        echo "Products count query: SUCCESS (Found $result products)\n";
        
        // Test brands query
        $brands = getAllBrands();
        echo "Brands query: SUCCESS (Found " . count($brands) . " brands)\n";
        
        // Test categories query  
        $categories = getAllCategories();
        echo "Categories query: SUCCESS (Found " . count($categories) . " categories)\n";
        
    } catch (Exception $e) {
        echo "Database connection: FAILED - " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

function fixCommonIssues() {
    echo "=== Fixing Common Issues ===\n\n";
    
    // Fix 1: Ensure proper directory structure
    echo "1. Creating/fixing directory structure...\n";
    checkAndFixPermissions();
    
    // Fix 2: Check PHP settings
    echo "2. Checking PHP upload settings...\n";
    echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
    echo "post_max_size: " . ini_get('post_max_size') . "\n";
    echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
    echo "file_uploads: " . (ini_get('file_uploads') ? 'ON' : 'OFF') . "\n";
    
    $uploadMaxBytes = ini_get('upload_max_filesize');
    if (substr($uploadMaxBytes, -1) == 'M') {
        $uploadMaxBytes = intval($uploadMaxBytes) * 1024 * 1024;
    }
    
    if ($uploadMaxBytes < MAX_FILE_SIZE) {
        echo "WARNING: PHP upload_max_filesize (" . ini_get('upload_max_filesize') . 
             ") is smaller than configured MAX_FILE_SIZE (" . (MAX_FILE_SIZE/1024/1024) . "MB)\n";
    }
    
    echo "\n";
    
    // Fix 3: Create .htaccess for uploads directory (if on Apache)
    echo "3. Creating .htaccess file for uploads directory...\n";
    $htaccessContent = "# Deny direct access to PHP files in uploads\n";
    $htaccessContent .= "<Files *.php>\n";
    $htaccessContent .= "    Deny from all\n";
    $htaccessContent .= "</Files>\n\n";
    $htaccessContent .= "# Allow image files\n";
    $htaccessContent .= "<FilesMatch \"\\.(jpg|jpeg|png|gif)$\">\n";
    $htaccessContent .= "    Allow from all\n";
    $htaccessContent .= "</FilesMatch>\n";
    
    if (file_put_contents('uploads/.htaccess', $htaccessContent)) {
        echo "Successfully created uploads/.htaccess\n";
    } else {
        echo "Could not create uploads/.htaccess (this is OK if using IIS)\n";
    }
    
    echo "\n";
}

// Run diagnostics and fixes
echo "Starting diagnostic and repair process...\n\n";

fixCommonIssues();
testDatabaseConnection();
testImageUpload();

// Final test from admin directory context
echo "=== Testing from Admin Directory Context ===\n\n";
chdir('admin');
echo "Changed to admin directory: " . getcwd() . "\n";
testImageUpload();

echo "=== Diagnostic Complete ===\n";
echo "If issues persist, please check:\n";
echo "1. Web server configuration (Apache/IIS/Nginx)\n";
echo "2. PHP-FPM or web server user permissions\n";  
echo "3. SELinux or similar security policies\n";
echo "4. Antivirus software blocking file operations\n";
?>
