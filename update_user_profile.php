<?php
require_once 'admin/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'update_profile') {
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address'] ?? '');

    // Check if email is already taken by another user
    $check_email = $conn->query("SELECT id FROM users WHERE email = '$email' AND id != $user_id");
    if ($check_email->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This email is already in use by another account']);
        exit;
    }

    if ($conn->query("UPDATE users SET name = '$name', phone = '$phone', email = '$email', address = '$address' WHERE id = $user_id")) {
        $_SESSION['user_name'] = $name; // Update session
        $_SESSION['user_email'] = $email; // Update session email
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
} 
elseif ($action === 'change_password') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
        exit;
    }

    $res = $conn->query("SELECT password FROM users WHERE id = $user_id");
    $user = $res->fetch_assoc();

    if (password_verify($current, $user['password'])) {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        if ($conn->query("UPDATE users SET password = '$hashed' WHERE id = $user_id")) {
            echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Current password incorrect']);
    }
}
else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
