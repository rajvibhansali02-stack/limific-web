<?php
require_once 'admin/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if user already exists using Prepared Statements
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $check = $stmt->get_result();
    
    if ($check->num_rows > 0) {
        $stmt->close();
        $redirect_query = isset($_POST['redirect']) ? "&redirect=" . urlencode($_POST['redirect']) : "";
        header("Location: login.php?mode=signup&error=Email already registered." . $redirect_query);
        exit;
    }
    $stmt->close();

    // Insert user using Prepared Statements
    $insertStmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
    $insertStmt->bind_param("ssss", $name, $email, $phone, $password);

    if ($insertStmt->execute()) {
        $_SESSION['user_id'] = $insertStmt->insert_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $insertStmt->close();
        
        $redirect = isset($_POST['redirect']) && !empty($_POST['redirect']) ? $_POST['redirect'] : 'index.php?welcome=1';
        header("Location: " . $redirect);
        exit;
    } else {
        $insertStmt->close();
        $redirect_query = isset($_POST['redirect']) ? "&redirect=" . urlencode($_POST['redirect']) : "";
        header("Location: login.php?mode=signup&error=Registration failed. Please try again." . $redirect_query);
    }
}
?>
