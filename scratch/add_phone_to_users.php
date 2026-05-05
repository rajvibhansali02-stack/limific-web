<?php
$conn = new mysqli('localhost', 'root', '', 'lumific_boutique');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) AFTER email");
echo "Added phone column to users table";
$conn->close();
?>
