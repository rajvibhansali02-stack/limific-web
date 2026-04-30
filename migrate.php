<?php
require_once 'admin/config.php';

$products = [
    [
        'name' => 'Spotboy 338',
        'category' => 'tracklights',
        'price' => 420.00,
        'color' => 'Black / Gold',
        'description' => 'Precision-engineered track light with adjustable beam angle.',
        'image_url' => 'images/shop_spotboy.webp',
        'badge' => 'New'
    ],
    [
        'name' => 'Barrel 320',
        'category' => 'tracklights',
        'price' => 380.00,
        'color' => 'Matte Black',
        'description' => 'Cylindrical track fixture for clean architectural lines.',
        'image_url' => 'images/shop_barrel.webp',
        'badge' => 'Popular'
    ],
    [
        'name' => 'Halo 394',
        'category' => 'downlights',
        'price' => 295.00,
        'color' => 'Obsidian Black',
        'description' => 'Recessed downlight with signature Lumific Halo ring optics.',
        'image_url' => 'images/shop_halo.webp',
        'badge' => 'Best Seller'
    ],
    [
        'name' => 'AllRounder 392',
        'category' => 'downlights',
        'price' => 180.00,
        'color' => 'Titanium Grey',
        'description' => 'Versatile architectural downlight for general ambient illumination.',
        'image_url' => 'images/shop_allrounder.webp',
        'badge' => 'Classic'
    ],
    [
        'name' => 'Go Pro 168',
        'category' => 'spots',
        'price' => 340.00,
        'color' => 'Brushed Gold',
        'description' => 'Professional spot luminaire for high-contrast accent lighting.',
        'image_url' => 'images/shop_gopro.webp',
        'badge' => 'Popular'
    ],
    [
        'name' => 'Baylight 418',
        'category' => 'outdoor',
        'price' => 890.00,
        'color' => 'Weatherproof Black',
        'description' => 'Robust outdoor luminaire designed for coastal and humid environments.',
        'image_url' => 'images/shop_baylight.webp',
        'badge' => 'New'
    ],
    [
        'name' => 'Iskim 126',
        'category' => 'profiles',
        'price' => 560.00,
        'color' => 'Aluminum Silver',
        'description' => 'Sleek linear profile for modern cove and shelf lighting.',
        'image_url' => 'images/shop_sconce.webp',
        'badge' => 'Classic'
    ],
    [
        'name' => 'Enso 30',
        'category' => 'ceiling',
        'price' => 2400.00,
        'color' => 'Ember Gold',
        'description' => 'Large-format ceiling ring from the Studio Abby series.',
        'image_url' => 'images/shop_flora.webp',
        'badge' => 'Best Seller'
    ]
];

foreach ($products as $p) {
    $name = $conn->real_escape_string($p['name']);
    $category = $p['category'];
    $price = $p['price'];
    $color = $conn->real_escape_string($p['color']);
    $description = $conn->real_escape_string($p['description']);
    $image_url = $p['image_url'];
    $badge = $p['badge'];

    $conn->query("INSERT INTO products (name, category, price, color, description, image_url, badge) 
                  VALUES ('$name', '$category', '$price', '$color', '$description', '$image_url', '$badge')");
}

echo "Migration Successful! 8 Products added to database.";
?>
