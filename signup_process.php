<?php
require_once 'admin/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $email_otp = $_POST['email_otp'] ?? '';
    $phone_otp = $_POST['phone_otp'] ?? '';

    // Verify Email OTP
    if (!isset($_SESSION['otp_email']) || $_SESSION['otp_email']['code'] != $email_otp || $_SESSION['otp_email']['expires'] < time()) {
        header("Location: login.php?mode=signup&error=Invalid or expired Email OTP.");
        exit;
    }

    // Verify Phone OTP
    if (!isset($_SESSION['otp_phone']) || $_SESSION['otp_phone']['code'] != $phone_otp || $_SESSION['otp_phone']['expires'] < time()) {
        header("Location: login.php?mode=signup&error=Invalid or expired Phone OTP.");
        exit;
    }

    // Check if user already exists
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        header("Location: login.php?mode=signup&error=Email already registered.");
        exit;
    }

    $sql = "INSERT INTO users (name, email, phone, password) VALUES ('$name', '$email', '$phone', '$password')";

    if ($conn->query($sql) === TRUE) {
        header("Location: login.php?success=Account created successfully. Please sign in.");
    } else {
        header("Location: login.php?mode=signup&error=Registration failed. Please try again.");
    }
}
?>
