<?php
require_once 'admin/config.php';
$res = $conn->query("SELECT * FROM products");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Name: " . $row['name'] . " | Image: " . $row['image_url'] . "\n";
}
?>
