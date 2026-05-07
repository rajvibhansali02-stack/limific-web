<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

/**
 * Send an email using PHPMailer
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $altBody Plain text version of the body
 * @return array ['success' => bool, 'message' => string]
 */
function sendMail($to, $subject, $body, $altBody = '') {
    $mail = new PHPMailer(true);

    try {
        // --- SMTP CONFIGURATION ---
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;         // Enable verbose debug output for testing
        $mail->isSMTP();                                  // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';             // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                         // Enable SMTP authentication
        $mail->Username   = 'YOUR_EMAIL@gmail.com';       // SMTP username
        $mail->Password   = 'YOUR_APP_PASSWORD';          // SMTP password (Use App Password for Gmail)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable implicit TLS encryption
        $mail->Port       = 587;                          // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        // --- RECIPIENTS ---
        $mail->setFrom('noreply@lumific.in', 'Lumific Boutique');
        $mail->addAddress($to);

        // --- CONTENT ---
        $mail->isHTML(true);                              // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);

        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"];
    }
}
