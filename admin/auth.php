<?php
require_once 'config.php';

// Simple Authentication for Lumific Boutique
// You can change these credentials here
$admin_user = "admin";
$admin_pass = "lumific2026"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    if ($user === $admin_user && $pass === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: dashboard.php");
    } else {
        header("Location: index.php?error=1");
    }
}
?>
