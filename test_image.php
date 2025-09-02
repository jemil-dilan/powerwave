<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

echo "Testing image display functionality:<br>";
echo "SITE_URL: " . SITE_URL . "<br>";

$imagePath = "test-image.png";
$imageUrl = getProductImageUrl($imagePath);

echo "Image path: " . $imagePath . "<br>";
echo "Generated URL: " . $imageUrl . "<br>";

echo "<br>Testing display:<br>";
echo "<img src='$imageUrl' style='border: 1px solid red;' alt='Test Image'><br>";

// Also test direct URL
$directUrl = SITE_URL . "/uploads/products/test-image.png";
echo "<br>Direct URL test: $directUrl<br>";
echo "<img src='$directUrl' style='border: 1px solid blue;' alt='Direct Test'><br>";

// Check file existence
$filePath = "uploads/products/test-image.png";
echo "<br>File exists check: " . (file_exists($filePath) ? "YES" : "NO") . "<br>";
echo "File path checked: " . realpath($filePath) . "<br>";

?>
