<?php
// Prevent any output before our JSON response
ob_start();

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once('admin/db_connect.php');
require_once 'vendor/autoload.php'; // Use Composer's autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send JSON response
function sendJsonResponse($status, $message) {
    ob_clean(); // Clear any previous output
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message
    ]);
    exit;
}

// Function to sanitize input
function sanitizeInput($data) {
    // Trim unnecessary spaces and remove unwanted characters (e.g., extra spaces, tabs)
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

try {
    // Check if it's a POST request
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        sendJsonResponse('error', 'Invalid request method');
    }

    // Sanitize and validate input data
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $mobile = sanitizeInput($_POST['mobile'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $street = sanitizeInput($_POST['street'] ?? '');
    $password = $_POST['password'] ?? '';

    // Check for empty fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($mobile) || empty($address) || empty($street) || empty($password)) {
        sendJsonResponse('error', 'All fields are required');
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse('error', 'Invalid email format');
    }

    // Validate mobile number: Only digits, length 11
    if (!preg_match('/^\d{11}$/', $mobile)) {
        sendJsonResponse('error', 'Invalid mobile number. Please enter an 11-digit number');
    }

    // Validate password
    if (strlen($password) < 8) {
        sendJsonResponse('error', 'Password must be at least 8 characters long');
    }

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT email FROM user_info WHERE email = ?");
    if (!$stmt) {
        sendJsonResponse('error', 'Database error: ' . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        sendJsonResponse('error', 'Email already exists');
    }
    $stmt->close();

    // Extract municipality from address
    $address_parts = explode(', ', $address);
    $municipality = end($address_parts);

    // Hash password using Argon2i
    $hashed_password = password_hash($password, PASSWORD_ARGON2I);

    // Generate 6-digit OTP
    $otp = sprintf("%06d", mt_rand(1, 999999));

    // Begin transaction
    $conn->begin_transaction();

    // Insert new user
    $insert_stmt = $conn->prepare("
        INSERT INTO user_info 
        (first_name, last_name, email, password, mobile, address, street, municipality, active, code) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?)
    ");
    if (!$insert_stmt) {
        sendJsonResponse('error', 'Database error: ' . $conn->error);
    }
    $insert_stmt->bind_param(
        "ssssssssi", 
        $first_name, $last_name, $email, $hashed_password, $mobile, 
        $address, $street, $municipality, $otp
    );
    if (!$insert_stmt->execute()) {
        sendJsonResponse('error', 'Registration failed: ' . $insert_stmt->error);
    }
    $insert_stmt->close();

    // Create new PHPMailer instance
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mandmcakeorderingsystem@gmail.com'; // Replace with your Gmail
        $mail->Password = 'dgld kvqo yecu wdka'; // Replace with your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('mandmcakeorderingsystem@gmail.com', 'M&M Cake Ordering System');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification Code';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2>Email Verification</h2>
                <p>Thank you for registering! Please use the following code to verify your email address:</p>
                <div style='background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px;'>
                    <strong>{$otp}</strong>
                </div>
                <p>This code will expire in 15 minutes.</p>
                <p>If you didn't request this verification, please ignore this email.</p>
            </div>
        ";
        $mail->AltBody = "Your verification code is: {$otp}";

        $mail->send();
    } catch (Exception $e) {
        throw new Exception("Error sending verification email: {$mail->ErrorInfo}");
    }

    // Store email in session for verification
    $_SESSION['verify_email'] = $email;

    // Commit transaction
    $conn->commit();

    // Success response
    sendJsonResponse('success', 'Registration successful! Please verify your email.');
} catch (Exception $e) {
    $conn->rollback();
    sendJsonResponse('error', 'An error occurred: ' . $e->getMessage());
}

$conn->close();
?>
