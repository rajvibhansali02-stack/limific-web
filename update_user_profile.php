<?php
require_once 'admin/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'update_profile') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'] ?? '';

    // Check if email is already taken using Prepared Statements
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $check_email = $stmt->get_result();
    
    if ($check_email->num_rows > 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'This email is already in use by another account']);
        exit;
    }
    $stmt->close();

    // Update profile using Prepared Statements
    $updateStmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, email = ?, address = ? WHERE id = ?");
    $updateStmt->bind_param("ssssi", $name, $phone, $email, $address, $user_id);

    if ($updateStmt->execute()) {
        $_SESSION['user_name'] = $name; // Update session
        $_SESSION['user_email'] = $email; // Update session email
        $updateStmt->close();
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        $updateStmt->close();
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

    // Retrieve password securely using Prepared Statements
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if (password_verify($current, $user['password'])) {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        
        // Update password securely using Prepared Statements
        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->bind_param("si", $hashed, $user_id);
        
        if ($updateStmt->execute()) {
            $updateStmt->close();
            echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
        } else {
            $updateStmt->close();
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
