<?php
// process_forgot_password.php
session_start();
include('admin/db_connect.php');

// Function to send OTP via Email
function send_otp_email($email, $otp) {
    // Import PHPMailer classes into the global namespace
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username = 'mandmcakeorderingsystem@gmail.com'; // Replace with your Gmail
        $mail->Password = 'dgld kvqo yecu wdka'; // Replace with your app password
         $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('mandmcakeorderingsystem@gmail.com', 'M&M Cake Ordering System');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body    = "Dear User,<br><br>Your OTP for password reset is <b>$otp</b>.<br>This OTP is valid for 10 minutes.<br><br>If you did not request a password reset, please ignore this email.<br><br>Regards,<br>Your Website Team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error message
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: forgot_password.php?message=" . urlencode("Invalid email format."));
        exit();
    }

    // Check if the email exists in the database
    $query = "SELECT user_id FROM user_info WHERE email = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        header("Location: forgot_password.php?message=" . urlencode("Database error."));
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // Email not found
        header("Location: forgot_password.php?message=" . urlencode("No account found with that email."));
        exit();
    }

    // Generate a 6-digit OTP
    $otp = random_int(100000, 999999);
    $otp_expiry = date("Y-m-d H:i:s", strtotime('+10 minutes'));

    // Update the user's OTP and expiry in the database
    $update_query = "UPDATE user_info SET otp = ?, otp_expiry = ? WHERE email = ?";
    $update_stmt = $conn->prepare($update_query);
    if (!$update_stmt) {
        header("Location: forgot_password.php?message=" . urlencode("Database error."));
        exit();
    }
    $update_stmt->bind_param("iss", $otp, $otp_expiry, $email);
    if (!$update_stmt->execute()) {
        header("Location: forgot_password.php?message=" . urlencode("Failed to generate OTP. Please try again."));
        exit();
    }

    // Send the OTP via email
    if (send_otp_email($email, $otp)) {
        // Redirect to OTP verification page
        header("Location: verify_otp.php?email=" . urlencode($email));
        exit();
    } else {
        header("Location: forgot_password.php?message=" . urlencode("Failed to send OTP. Please try again."));
        exit();
    }
} else {
    header("Location: forgot_password.php");
    exit();
}
?>
