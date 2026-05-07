<?php
require_once 'admin/config.php';
$sql = "UPDATE products SET image_url = REPLACE(image_url, '.png', '.webp') WHERE image_url LIKE '%.png'";
if ($conn->query($sql)) {
    echo "Updated " . $conn->affected_rows . " product image paths from .png to .webp";
} else {
    echo "Error: " . $conn->error;
}
?>
