<?php
require_once 'admin/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);



    // Check if user already exists
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $redirect_query = isset($_POST['redirect']) ? "&redirect=" . urlencode($_POST['redirect']) : "";
        header("Location: login.php?mode=signup&error=Email already registered." . $redirect_query);
        exit;
    }

    $sql = "INSERT INTO users (name, email, phone, password) VALUES ('$name', '$email', '$phone', '$password')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $redirect = isset($_POST['redirect']) && !empty($_POST['redirect']) ? $_POST['redirect'] : 'index.php?welcome=1';
        header("Location: " . $redirect);
        exit;
    } else {
        $redirect_query = isset($_POST['redirect']) ? "&redirect=" . urlencode($_POST['redirect']) : "";
        header("Location: login.php?mode=signup&error=Registration failed. Please try again." . $redirect_query);
    }
}
?>
