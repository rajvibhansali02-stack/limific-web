<?php
require_once 'admin/config.php';

$mapping = [
    'tracklights' => 'images/cat_tracklights.webp',
    'downlights' => 'images/cat_downlights.webp',
    'spots' => 'images/cat_spots.webp',
    'outdoor' => 'images/cat_outdoor.webp',
    'profiles' => 'images/cat_profiles.webp',
    'ceiling' => 'images/cat_studio_abby.webp'
];

foreach ($mapping as $cat => $img) {
    $conn->query("UPDATE products SET image_url = '$img' WHERE category = '$cat' AND (image_url LIKE 'images/shop_%' OR image_url = '')");
}

echo "Database paths updated to use existing category placeholders.";
?>
