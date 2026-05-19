<?php
require_once 'admin/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Secure user retrieval using Prepared Statements
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['login_success'] = "Login successfully";
            $redirect = isset($_POST['redirect']) && !empty($_POST['redirect']) ? $_POST['redirect'] : 'index.php';
            header("Location: " . $redirect);
            exit;
        } else {
            $redirect_query = isset($_POST['redirect']) ? "&redirect=" . urlencode($_POST['redirect']) : "";
            header("Location: login.php?error=Incorrect password." . $redirect_query);
            exit;
        }
    } else {
        $stmt->close();
        $redirect_query = isset($_POST['redirect']) ? "&redirect=" . urlencode($_POST['redirect']) : "";
        header("Location: login.php?error=User not found." . $redirect_query);
        exit;
    }
}
?>
