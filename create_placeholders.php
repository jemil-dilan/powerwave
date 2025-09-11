<?php
// Create placeholder images using GD library

function createPlaceholderImage($width, $height, $text, $filename) {
    $image = imagecreate($width, $height);
    
    // Colors
    $background = imagecolorallocate($image, 240, 240, 240);
    $textColor = imagecolorallocate($image, 100, 100, 100);
    $borderColor = imagecolorallocate($image, 200, 200, 200);
    
    // Fill background
    imagefill($image, 0, 0, $background);
    
    // Add border
    imagerectangle($image, 0, 0, $width-1, $height-1, $borderColor);
    
    // Add text
    $font = 3; // Built-in font
    $textWidth = imagefontwidth($font) * strlen($text);
    $textHeight = imagefontheight($font);
    
    $x = ($width - $textWidth) / 2;
    $y = ($height - $textHeight) / 2;
    
    imagestring($image, $font, $x, $y, $text, $textColor);
    
    // Save image
    imagejpeg($image, $filename, 80);
    imagedestroy($image);
}

// Create placeholder images
$images = [
    'no-image.jpg' => [300, 300, 'No Image Available'],
    'hero-outboard.jpg' => [600, 400, 'Outboard Motor Hero Image'],
    'category-placeholder.jpg' => [300, 200, 'Category Image'],
];

foreach ($images as $filename => $config) {
    $path = "images/$filename";
    createPlaceholderImage($config[0], $config[1], $config[2], $path);
    echo "Created: $path\n";
}

echo "Placeholder images created successfully!\n";
?>
