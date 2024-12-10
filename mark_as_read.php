<?php
session_start();
include 'admin/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['login_user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$userId = $_SESSION['login_user_id'];
$notificationId = $_POST['notification_id'];

// Validate input
if (empty($notificationId)) {
    echo json_encode(['success' => false, 'error' => 'Invalid notification ID']);
    exit();
}

// Prepare and execute update query
$query = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND order_number = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $userId, $notificationId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>