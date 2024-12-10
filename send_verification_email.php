<?php
session_start();
require 'mailer_config.php';  // Include the mailer configuration

$mail = configureMailer();

// Assuming you have already generated a verification code
$verificationCode = rand(100000, 999999); // Generate a random 6-digit code

// Store the code in the session
$_SESSION['verification_code'] = $verificationCode;

// Recipient's email
$recipientEmail = 'recipient@example.com'; // Replace with actual email

// Add a recipient
$mail->addAddress($recipientEmail);

// Content
$mail->isHTML(true);  // Set email format to HTML
$mail->Subject = 'Email Verification Code';
$mail->Body    = 'Your verification code is: <b>' . $verificationCode . '</b>';
$mail->AltBody = 'Your verification code is: ' . $verificationCode; // Plain text for non-HTML email clients

try {
    $mail->send();
    echo 'Verification code has been sent to your email.';
} catch (Exception $e) {
    echo "Verification code could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
