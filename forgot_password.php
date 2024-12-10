<?php
require_once('admin/db_connect.php'); // Database connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Autoload PHPMailer using Composer

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Set the default timezone to Asia/Manila (or any desired Asia timezone)
    date_default_timezone_set('Asia/Manila');

    // Check if the email exists
    $stmt = $conn->prepare("SELECT user_id, first_name FROM user_info WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userId = $user['user_id'];
        $firstName = $user['first_name'];

        // Generate a secure token, reset code, and expiry time
        $resetToken = bin2hex(random_bytes(32)); // Secure token
        $resetCode = random_int(100000, 999999); // 6-digit reset code
        $tokenExpiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Set expiry time (1 hour from now)

        // Set otp_expiry to the current time + 1 hour (Unix timestamp)
        $otpExpiry = time() + 3600; // current time + 1 hour in seconds

        // Save the token, reset code, otp_expiry, and token_expiry to the database
        $updateStmt = $conn->prepare("UPDATE user_info SET token = ?, otp = ?, token_expiry = ?, otp_expiry = FROM_UNIXTIME(?) WHERE user_id = ?");
        $updateStmt->bind_param("sisii", $resetToken, $resetCode, $tokenExpiry, $otpExpiry, $userId);
        $updateStmt->execute();

        // Prepare the email content
        $resetLink = "https://mandm-lawis.com/reset_password.php?token={$resetToken}";
        $emailContent = "
            <p>You have requested to reset your password.</p>
            <p>Your reset code is: <strong>{$resetCode}</strong></p>
            <p>Click the link below to reset your password:</p>
            <p><a href='{$resetLink}'>Reset Password</a></p>
            <p>If you did not request this, please ignore this email.</p>
        ";

        // Send the email
        $mail = new PHPMailer(true);
        try {
            // Mail server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'mandmcakeorderingsystem@gmail.com'; // Replace with your email
            $mail->Password   = 'dgld kvqo yecu wdka'; // Replace with your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Email content
            $mail->setFrom('mandmcakeorderingsystem@gmail.com', 'M&M Cake Ordering System'); // Sender
            $mail->addAddress($email, $firstName); // Recipient
            $mail->Subject = 'Password Reset Request';
            $mail->isHTML(true); // Enable HTML content
            $mail->Body = $emailContent;

            // Send the email
            $mail->send();
            echo json_encode(['status' => 'success', 'message' => 'Reset password email sent successfully.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send email. Error: ' . $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email address not found.']);
    }
}
?>
