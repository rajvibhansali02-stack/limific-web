<?php
require_once 'admin/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['login_success'] = "Login successfully";
            header("Location: index.php");
            exit;
        } else {
            header("Location: login.php?error=Incorrect password.");
            exit;
        }
    } else {
        header("Location: login.php?error=User not found.");
        exit;
    }
}
?>
