<?php
require_once '../admin/config.php';
$result = $conn->query("SELECT * FROM products");
$products = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($products, JSON_PRETTY_PRINT);
?>
