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

    // --- LIVE INTEGRATION ---
    require_once 'includes/mailer.php';
    
    $success = true;
    $message = "OTP sent successfully.";
    $is_demo = true; // Set to FALSE when you have entered your SMTP credentials in includes/mailer.php

    if (!$is_demo) {
        if ($type === 'email') {
            $subject = "Your Lumific Verification Code";
            $body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                    <h2 style='color: #000;'>Lumific Boutique Verification</h2>
                    <p>Your verification code is:</p>
                    <div style='font-size: 32px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;'>$otp</div>
                    <p>This code is valid for 5 minutes. Do not share it with anyone.</p>
                </div>
            ";
            $result = sendMail($target, $subject, $body);
            $success = $result['success'];
            $message = $result['message'];
        } else {
            // SMS logic would go here (requires SMS API)
            $message = "SMS OTP simulation: $otp";
        }
    }

    $response = [
        'success' => $success,
        'message' => $message
    ];

    // ONLY send OTP in response if in demo mode
    if ($is_demo) {
        $response['otp'] = $otp;
        $response['message'] .= " (Demo Mode)";
    }

    echo json_encode($response);
}
?>
