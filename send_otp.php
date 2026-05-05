<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $target = $_POST['target'] ?? '';
    
    if (empty($target)) {
        echo json_encode(['success' => false, 'message' => 'Target is required.']);
        exit;
    }

    // Generate 6-digit OTP
    $otp = rand(100000, 999999);
    
    // Store in session with expiry (5 mins)
    $_SESSION['otp_' . $type] = [
        'code' => $otp,
        'target' => $target,
        'expires' => time() + 300
    ];

    // Simulate sending
    // In production, use PHPMailer or SMS API here
    $success = true;
    $message = "OTP sent successfully.";

    // Example of how you would send email if mail() works:
    if ($type === 'email') {
        $to = $target;
        $subject = "Your Lumific Verification Code";
        $body = "Your verification code is: $otp\n\nValid for 5 minutes.";
        $headers = "From: no-reply@lumific.in";
        // @mail($to, $subject, $body, $headers); // Uncomment in production
    }

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'otp' => $otp // Sending OTP in response for DEMO purposes ONLY
    ]);
}
?>
