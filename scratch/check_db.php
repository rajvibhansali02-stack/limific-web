<?php
require_once 'admin/config.php';
$res = $conn->query("SELECT id, name, image_url FROM products");
$rows = $res->fetch_all(MYSQLI_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT);
?>
