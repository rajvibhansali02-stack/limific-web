<?php
$conn = new mysqli('localhost', 'root', '', 'lumific_boutique');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL AFTER order_id");
echo "Added user_id column to orders table";
$conn->close();
?>
