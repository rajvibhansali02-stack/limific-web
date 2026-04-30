<?php
require_once 'admin/config.php';

$restoration = [
    'Spotboy 338' => 'images/shop_spotboy.webp',
    'Barrel 320' => 'images/shop_barrel.webp',
    'Halo 394' => 'images/shop_halo.webp',
    'AllRounder 392' => 'images/shop_allrounder.webp',
    'Go Pro 168' => 'images/shop_gopro.webp',
    'Baylight 418' => 'images/shop_baylight.webp',
    'Iskim 126' => 'images/shop_sconce.webp',
    'Enso 30' => 'images/shop_flora.webp'
];

foreach ($restoration as $name => $img) {
    $name_escaped = $conn->real_escape_string($name);
    $conn->query("UPDATE products SET image_url = '$img' WHERE name = '$name_escaped'");
}

echo "Original image paths restored in database.";
?>
