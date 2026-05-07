<?php
require_once 'admin/config.php';
$res = $conn->query("SELECT id, name, description FROM products");
while($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']} | Name: {$row['name']} | Desc: '" . substr($row['description'], 0, 50) . "...'\n";
}
?>
