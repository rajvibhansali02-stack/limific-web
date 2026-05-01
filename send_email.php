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

    $subject = "New Inquiry: " . $product;
    
    // Create the email content
    $email_content = "New inquiry from Lumific Luxury Lighting Boutique:\n\n";
    $email_content .= "Client Name: $name\n";
    $email_content .= "Client Email: $email\n";
    $email_content .= "Client Phone: $phone\n";
    $email_content .= "Interest: $product\n";
    $email_content .= "Message:\n$message\n\n";
    $email_content .= "--- End of Message ---";

    // Build the email headers
    $headers = "From: Lumific Boutique <noreply@lumific.in>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Check if we are running locally (XAMPP/Localhost)
    $is_local = ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1');

    if (mail($to, $subject, $email_content, $headers)) {
        echo json_encode(["status" => "success", "message" => "Your inquiry has been sent successfully!"]);
    } elseif ($db_saved) {
        // If DB save was successful, we consider it a success for local testing even if mail() fails
        echo json_encode([
            "status" => "success", 
            "message" => "Your inquiry has been received (Local Mode)."
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Something went wrong. Please try again later."]);
    }
} else {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Access denied."]);
}
?>
