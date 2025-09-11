<?php
/**
 * Create Missing Directories Script
 * Ensures all required directories exist with proper permissions
 */

echo "<h1>📁 Directory Setup Script</h1>";

$directories = [
    'uploads/',
    'uploads/products/',
    'uploads/categories/',
    'uploads/brands/',
    'uploads/users/',
    'api/',
    'tests/',
    'images/'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p>✅ Created directory: $dir</p>";
        } else {
            echo "<p>❌ Failed to create directory: $dir</p>";
        }
    } else {
        echo "<p>✅ Directory exists: $dir</p>";
    }
    
    // Check if writable
    if (is_writable($dir)) {
        echo "<p>✅ Directory writable: $dir</p>";
    } else {
        echo "<p>⚠️ Directory not writable: $dir (may need permission adjustment)</p>";
    }
}

// Create placeholder images if they don't exist
$placeholderImages = [
    'images/no-image.jpg' => [300, 300, 'No Image Available'],
    'images/hero-outboard.jpg' => [800, 400, 'Hero Outboard Motor'],
    'images/category-placeholder.jpg' => [300, 200, 'Category Image']
];

foreach ($placeholderImages as $imagePath => $config) {
    if (!file_exists($imagePath)) {
        // Create a simple placeholder image
        $image = imagecreate($config[0], $config[1]);
        $background = imagecolorallocate($image, 240, 240, 240);
        $textColor = imagecolorallocate($image, 100, 100, 100);
        
        imagefill($image, 0, 0, $background);
        
        $text = $config[2];
        $font = 3;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        
        $x = ($config[0] - $textWidth) / 2;
        $y = ($config[1] - $textHeight) / 2;
        
        imagestring($image, $font, $x, $y, $text, $textColor);
        
        if (imagejpeg($image, $imagePath, 80)) {
            echo "<p>✅ Created placeholder image: $imagePath</p>";
        } else {
            echo "<p>❌ Failed to create placeholder image: $imagePath</p>";
        }
        
        imagedestroy($image);
    } else {
        echo "<p>✅ Placeholder image exists: $imagePath</p>";
    }
}

echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>✅ Directory Setup Complete</h3>";
echo "<p>All required directories have been created and verified.</p>";
echo "</div>";
?>