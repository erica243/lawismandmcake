<?php
// forgot_password.php

require_once('admin/db_connect.php'); // Include database connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Include PHPMailer

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT user_id, first_name FROM user_info WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userId = $user['user_id'];
        $firstName = $user['first_name'];

        // Generate reset token and other parameters
        $resetToken = bin2hex(random_bytes(32)); // Secure token
        $resetCode = random_int(100000, 999999); // 6-digit reset code
        $tokenExpiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Expiry time

        // Save token and other info in the database
        $updateStmt = $conn->prepare("UPDATE user_info SET token = ?, otp = ?, token_expiry = ? WHERE user_id = ?");
        $updateStmt->bind_param("ssis", $resetToken, $resetCode, $tokenExpiry, $userId);
        $updateStmt->execute();

        // Send email with the reset link
        $resetLink = "https://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token={$resetToken}";
        $emailContent = "
            <p>You requested a password reset.</p>
            <p>Your reset code is: <strong>{$resetCode}</strong></p>
            <p>Click <a href='{$resetLink}'>here</a> to reset your password.</p>
        ";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'mandmcakeorderingsystem@gmail.com';
            $mail->Password = 'your-app-password';  // Use your Gmail App password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->setFrom('mandmcakeorderingsystem@gmail.com', 'M&M Cake Ordering System');
            $mail->addAddress($email, $firstName);
            $mail->Subject = 'Password Reset Request';
            $mail->isHTML(true);
            $mail->Body = $emailContent;

            $mail->send();
            echo json_encode(['status' => 'success', 'message' => 'A reset link has been sent to your email.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send email. Please try again.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email address not found.']);
    }
}
?>
