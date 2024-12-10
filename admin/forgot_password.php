<?php
require_once('db_connect.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

// Set default timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

function generateResetToken($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

function generateResetCode($length = 6) {
    return str_pad(mt_rand(0, 999999), $length, '0', STR_PAD_LEFT);
}

function sendPasswordResetEmail($email, $resetLink, $resetCode) {
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mandmcakeorderingsystem@gmail.com';
        $mail->Password   = 'dgld kvqo yecu wdka';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Email content
        $mail->setFrom('mandmcakeorderingsystem@gmail.com', 'M&M Cake Ordering System');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = "
            <p>You have requested to reset your password.</p>
            <p>Your reset code is: <strong>{$resetCode}</strong></p>
            <p>Click the link below to reset your password:</p>
            <p><a href='{$resetLink}'>Reset Password</a></p>
            <p>If you did not request this, please ignore this email.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function handleForgotPassword($conn) {
    // Validate email input
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Invalid email address.'
        ]);
        exit;
    }

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'No account found with this email.'
        ]);
        exit;
    }

    // Generate reset token and code
    $resetToken = generateResetToken();
    $resetCode = generateResetCode();
    $resetCodeExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    $resetLink = "http://localhost/1/admin/reset_password.php?token={$resetToken}";

    // Update user's reset information
    $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_code = ?, reset_code_expiry = ? WHERE email = ?");
    $updateStmt->bind_param("ssss", $resetToken, $resetCode, $resetCodeExpiry, $email);
    
    if ($updateStmt->execute() && sendPasswordResetEmail($email, $resetLink, $resetCode)) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Password reset link sent to your email.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Unable to send reset link. Please try again.'
        ]);
    }

    $stmt->close();
    $updateStmt->close();
}

// Handle forgot password AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'forgot_password') {
    handleForgotPassword($conn);
}
?>