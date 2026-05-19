<?php
header('Content-Type: application/json');

// Set the recipient email address
$to = "lumificlighting@gmail.com";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '';
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
    $message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '';
    $product = isset($_POST['product']) ? htmlspecialchars($_POST['product']) : "General Inquiry";
    
    if (empty($email) || empty($message)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Please fill all fields."]);
        exit;
    }

    // --- DATABASE STORAGE ---
    require_once 'admin/config.php';
    $stmt = $conn->prepare("INSERT INTO inquiries (name, email, phone, product, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $phone, $product, $message);
    $db_saved = $stmt->execute();

    if ($db_saved) {
        echo json_encode(["status" => "success", "message" => "Your inquiry has been sent successfully!"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database error."]);
    }
} else {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Access denied."]);
}
?>
