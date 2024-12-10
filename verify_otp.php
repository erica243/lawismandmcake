<?php
session_start();
require_once('admin/db_connect.php');

// Function to send JSON response
function sendJsonResponse($status, $message) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message
    ]);
    exit;
}

try {
    // Check if the user is coming from the OTP page
    if (!isset($_SESSION['verify_email'])) {
        sendJsonResponse('error', 'Session expired. Please sign up again.');
    }

    // Check if it's a POST request
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        sendJsonResponse('error', 'Invalid request method');
    }

    // Get the OTP from the request
    $otp = $_POST['otp'] ?? '';

    // Validate OTP
    if (!preg_match('/^\d{6}$/', $otp)) {
        sendJsonResponse('error', 'Invalid OTP format');
    }

    // Get the email from the session
    $email = $_SESSION['verify_email'];

    // Check if the OTP matches the one in the database
    $stmt = $conn->prepare("SELECT user_id FROM user_info WHERE email = ? AND code = ?");
    if (!$stmt) {
        sendJsonResponse('error', 'Database error: ' . $conn->error);
    }
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendJsonResponse('error', 'Invalid OTP. Please try again.');
    }

    // If OTP is valid, update the user's active status
    $stmt = $conn->prepare("UPDATE user_info SET active = 1 WHERE email = ?");
    if (!$stmt) {
        sendJsonResponse('error', 'Database error: ' . $conn->error);
    }
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        sendJsonResponse('error', 'Failed to activate account: ' . $stmt->error);
    }

    // Clear the session variable
    unset($_SESSION['verify_email']);

    // Success response
    sendJsonResponse('success', 'Account activated successfully! You can now log in.');

} catch (Exception $e) {
    sendJsonResponse('error', 'An error occurred: ' . $e->getMessage());
}

$conn->close();
?>