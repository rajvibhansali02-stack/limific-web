<?php
header('Content-Type: application/json');

// Set the recipient email address
$to = "rajvibhansali02@gmail.com";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
    $message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '';
    $product = isset($_POST['product']) ? htmlspecialchars($_POST['product']) : "General Inquiry";
    
    if (empty($email) || empty($message)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Please fill all fields."]);
        exit;
    }

    $subject = "New Inquiry: " . $product;
    
    // Create the email content
    $email_content = "New inquiry from Lumific Luxury Lighting Boutique:\n\n";
    $email_content .= "Client Email: $email\n";
    $email_content .= "Interest: $product\n";
    $email_content .= "Message:\n$message\n\n";
    $email_content .= "--- End of Message ---";

    // Build the email headers
    $headers = "From: Lumific Boutique <noreply@lumific.in>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Send the email
    if (mail($to, $subject, $email_content, $headers)) {
        echo json_encode(["status" => "success", "message" => "Your inquiry has been sent successfully!"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Something went wrong. Please try again later."]);
    }
} else {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Access denied."]);
}
?>
