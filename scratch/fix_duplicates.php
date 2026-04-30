<?php
require_once '../admin/config.php';

// Fix Shield 20 image path
$conn->query("UPDATE products SET image_url = 'images/shield_20.png' WHERE name = 'Shield 20 Anti-Glare Snoot'");

// Copy a distinct image to shield_20.png for now
// I'll use luna_100.png as a temporary high-end placeholder if it exists
if (file_exists('../images/luna_100.png')) {
    copy('../images/luna_100.png', '../images/shield_20.png');
} else {
    // If not, just copy the logo or something different
    copy('../images/logo.png', '../images/shield_20.png');
}

echo "Database updated. Shield 20 now has its own image path.";
?>
