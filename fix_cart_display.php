<?php
echo "Fixing cart display across all PHP files...\n";

$files = [
    'accessories.php',
    'account.php', 
    'contact.php',
    'returns.php',
    'shipping.php',
    'brand.php',
    'faq.php',
    'brands.php',
    'products.php',
    'product.php',
    'about.php',
    'accessory.php',
    'warranty.php',
    'search.php'
];

$search = 'formatPrice(getCartTotal(isLoggedIn() ? $_SESSION[\'user_id\'] : null))';
$replace = 'getCartTotalForDisplay(isLoggedIn() ? $_SESSION[\'user_id\'] : null)';

$updatedFiles = 0;

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        if (strpos($content, $search) !== false) {
            $newContent = str_replace($search, $replace, $content);
            file_put_contents($file, $newContent);
            echo "✅ Updated: $file\n";
            $updatedFiles++;
        } else {
            echo "⚠️  No match found in: $file\n";
        }
    } else {
        echo "❌ File not found: $file\n";
    }
}

echo "\nCompleted! Updated $updatedFiles files.\n";
echo "Cart will now show empty when no items are present.\n";
?>