<?php
require_once 'config.php';
session_start();

// Simple Authentication for Lumific Boutique
// You can change these credentials here
$admin_user = "admin";
$admin_pass = "lumific2026"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    if ($user !== $admin_user) {
        header("Location: index.php?error=user");
        exit;
    }
    
    if ($pass !== $admin_pass) {
        header("Location: index.php?error=pass");
        exit;
    }

    $_SESSION['admin_logged_in'] = true;
    header("Location: dashboard.php");
    exit;
}
?>
