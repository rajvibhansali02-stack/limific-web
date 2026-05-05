<?php
require_once '../admin/config.php';

// Fix Shield 20 image path
$conn->query("UPDATE products SET image_url = 'images/shield_20.webp' WHERE name = 'Shield 20 Anti-Glare Snoot'");

// Copy a distinct image to shield_20.webp for now
// I'll use luna_100.webp as a temporary high-end placeholder if it exists
if (file_exists('../images/luna_100.webp')) {
    copy('../images/luna_100.webp', '../images/shield_20.webp');
} else {
    // If not, just copy the logo or something different
    copy('../images/logo.webp', '../images/shield_20.webp');
}

echo "Database updated. Shield 20 now has its own image path.";
?>
