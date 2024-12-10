<?php
session_start();
include 'admin/db_connect.php';

if (!isset($_SESSION['login_user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['login_user_id'];

// Query to get unread count
$query = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$unreadCount = $result->fetch_assoc()['unread'] ?? 0;

echo json_encode($unreadCount);
$stmt->close();
$conn->close();
?>
