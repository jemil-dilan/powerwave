<?php
/**
 * WaveMaster Branding Update Script
 * This script updates all files to replace PowerWave with WaveMaster Outboards
 */

echo "=== WaveMaster Branding Update Script ===\n";
echo "Updating all files with WaveMaster branding...\n\n";

// Files to update (excluding the update script itself and certain files)
$filesToUpdate = [
    'accessories.php',
    'about.php', 
    'account.php',
    'add_to_cart.php',
    'brands.php',
    'cart.php',
    'checkout.php',
    'contact.php',
    'faq.php',
    'login.php',
    'logout.php',
    'order_success.php',
    'privacy.php',
    'product.php', 
    'products.php',
    'register.php',
    'returns.php',
    'search.php',
    'shipping.php',
    'terms.php',
    // Admin files
    'admin/index.php',
    'admin/add_product.php',
    'admin/edit_product.php',
    'admin/orders.php',
    'admin/products.php',
    'admin/users.php',
    'admin/view_order.php'
];

// Branding replacements
$replacements = [
    'PowerWave outboards' => 'WaveMaster Outboards',
    'PowerWave Outboards' => 'WaveMaster Outboards', 
    'PowerWave' => 'WaveMaster',
    'powerwave' => 'wavemaster',
    'POWERWAVE' => 'WAVEMASTER',
    'logo1.png' => 'wave.jpeg',
    'PowerWave@outboard.com' => 'info@wavemasteroutboards.com',
    'PowerWave@outboardmotorspro.com' => 'admin@wavemasteroutboards.com',
    'info@outboardmotorspro.com' => 'info@wavemasteroutboards.com'
];

$filesUpdated = 0;
$totalReplacements = 0;

foreach ($filesToUpdate as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $originalContent = $content;
        $fileReplacements = 0;
        
        foreach ($replacements as $search => $replace) {
            $count = 0;
            $content = str_replace($search, $replace, $content, $count);
            $fileReplacements += $count;
        }
        
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            echo "✓ Updated: $file ($fileReplacements replacements)\n";
            $filesUpdated++;
            $totalReplacements += $fileReplacements;
        } else {
            echo "- No changes needed: $file\n";
        }
    } else {
        echo "! File not found: $file\n";
    }
}

echo "\n=== Update Complete ===\n";
echo "Files updated: $filesUpdated\n";
echo "Total replacements: $totalReplacements\n";
echo "\nWaveMaster branding has been applied across the website!\n";
?>
