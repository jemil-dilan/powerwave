<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

echo "<h2>🖼️ Product Image Upload Test</h2>";
echo "<p>This test specifically focuses on image uploads for product creation.</p>";

// Check directory
$uploadDir = '../uploads/products/';
echo "<h3>📁 Directory Check</h3>";
echo "<p><strong>Upload directory:</strong> " . realpath($uploadDir) . "</p>";
echo "<p><strong>Directory writable:</strong> " . (is_writable($uploadDir) ? '✅ YES' : '❌ NO') . "</p>";

// Process image upload
if ($_POST && isset($_FILES['product_image'])) {
    echo "<hr><h3>📤 Image Upload Results</h3>";
    
    echo "<h4>Raw Upload Data:</h4>";
    echo "<pre>";
    print_r($_FILES['product_image']);
    echo "</pre>";
    
    $file = $_FILES['product_image'];
    
    // Check if file was actually uploaded
    if ($file['error'] === UPLOAD_ERR_OK) {
        echo "<p>✅ <strong>File upload status:</strong> OK</p>";
        
        // Validate image type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        echo "<p><strong>File extension:</strong> $fileExt</p>";
        echo "<p><strong>Allowed types:</strong> " . implode(', ', $allowedTypes) . "</p>";
        
        if (in_array($fileExt, $allowedTypes)) {
            echo "<p>✅ <strong>File type:</strong> Valid</p>";
            
            // Check file size
            $maxSize = 5 * 1024 * 1024; // 5MB
            echo "<p><strong>File size:</strong> " . number_format($file['size']) . " bytes</p>";
            echo "<p><strong>Max allowed:</strong> " . number_format($maxSize) . " bytes</p>";
            
            if ($file['size'] <= $maxSize) {
                echo "<p>✅ <strong>File size:</strong> Valid</p>";
                
                // Validate MIME type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                echo "<p><strong>MIME type:</strong> $mimeType</p>";
                
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($mimeType, $allowedMimes)) {
                    echo "<p>✅ <strong>MIME type:</strong> Valid</p>";
                    
                    // Generate unique filename
                    $newFileName = 'product_' . uniqid() . '.' . $fileExt;
                    $destination = $uploadDir . $newFileName;
                    
                    echo "<p><strong>Destination:</strong> $destination</p>";
                    
                    // Move the uploaded file
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 IMAGE UPLOAD SUCCESSFUL!</p>";
                        echo "<p><strong>Saved as:</strong> $newFileName</p>";
                        echo "<p><strong>Full path:</strong> " . realpath($destination) . "</p>";
                        echo "<p><strong>File size on disk:</strong> " . filesize($destination) . " bytes</p>";
                        
                        // Show the uploaded image
                        $webPath = '../uploads/products/' . $newFileName;
                        echo "<h4>📷 Uploaded Image Preview:</h4>";
                        echo "<img src='$webPath' style='max-width: 300px; border: 2px solid #28a745; border-radius: 8px; margin: 10px 0;'>";
                        
                        // Test the getProductImageUrl function
                        echo "<h4>🔗 Image URL Test:</h4>";
                        $imageUrl = getProductImageUrl($newFileName);
                        echo "<p><strong>Generated URL:</strong> <a href='$imageUrl' target='_blank'>$imageUrl</a></p>";
                        
                    } else {
                        echo "<p style='color: red; font-weight: bold;'>❌ FAILED TO MOVE UPLOADED FILE</p>";
                        echo "<p>Check directory permissions and disk space.</p>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ Invalid MIME type: $mimeType</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ File too large</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Invalid file extension: $fileExt</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Upload error code: " . $file['error'] . "</p>";
        
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload'
        ];
        
        $errorMsg = $errorMessages[$file['error']] ?? 'Unknown error';
        echo "<p><strong>Error meaning:</strong> $errorMsg</p>";
    }
}
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    margin: 20px; 
    background: #f8f9fa; 
}
h2, h3 { 
    color: #333; 
}
h2 { 
    color: #0066cc; 
    border-bottom: 2px solid #0066cc; 
    padding-bottom: 10px; 
}
pre { 
    background: #fff; 
    padding: 15px; 
    border: 1px solid #ddd; 
    border-radius: 5px; 
    overflow-x: auto;
    font-size: 12px;
}
.form-container {
    background: white;
    padding: 30px;
    border-radius: 10px;
    border: 2px solid #0066cc;
    max-width: 600px;
    margin: 20px 0;
}
.upload-btn {
    background: #0066cc;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
}
.upload-btn:hover {
    background: #0056b3;
}
.file-input {
    margin: 15px 0;
    padding: 10px;
    border: 2px dashed #0066cc;
    border-radius: 5px;
    background: #f0f8ff;
}
</style>

<div class="form-container">
    <h3>🖼️ Upload Product Image</h3>
    <p>Select an image file (JPG, PNG, GIF) to test the product image upload:</p>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="file-input">
            <input type="file" name="product_image" accept="image/jpeg,image/png,image/gif" required>
        </div>
        <button type="submit" class="upload-btn">📤 Upload Image</button>
    </form>
    
    <div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-radius: 5px; border-left: 4px solid #0066cc;">
        <strong>💡 Instructions:</strong>
        <ul style="margin: 10px 0;">
            <li>Choose a JPG, PNG, or GIF image file</li>
            <li>File should be under 5MB</li>
            <li>Click "Upload Image" to test</li>
            <li>Check the results above</li>
        </ul>
    </div>
</div>

<div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0;">
    <strong>🔧 If upload fails:</strong>
    <ol>
        <li>Check that the uploads/products directory is writable</li>
        <li>Verify PHP file upload settings</li>
        <li>Make sure the file is a valid image</li>
        <li>Try with a smaller file size</li>
    </ol>
</div>
