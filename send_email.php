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

    // --- EMAIL SENDING (PHPMailer) ---
    require_once 'includes/mailer.php';
    
    $subject = "New Inquiry: " . $product;
    $body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee;'>
            <h2 style='color: #2d241e;'>New Inquiry from Lumific</h2>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Interest:</strong> $product</p>
            <p><strong>Message:</strong></p>
            <div style='background: #f9f9f9; padding: 15px; border-left: 4px solid #2d241e;'>$message</div>
        </div>
    ";

    $is_demo = true; // Set to FALSE when you have entered your SMTP credentials in includes/mailer.php

    if (!$is_demo) {
        $mail_result = sendMail($to, $subject, $body);
        if ($mail_result['success']) {
            echo json_encode(["status" => "success", "message" => "Your inquiry has been sent successfully!"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Mail Error: " . $mail_result['message']]);
        }
    } else {
        // Local/Demo Mode
        if ($db_saved) {
            echo json_encode([
                "status" => "success", 
                "message" => "Inquiry received! (Demo Mode: Data saved to database)"
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Database error."]);
        }
    }
} else {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Access denied."]);
}
?>
