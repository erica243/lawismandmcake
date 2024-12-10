<?php
session_start();
include 'admin/db_connect.php';

if (!isset($_SESSION['login_user_id'])) {
    die("User not logged in");
}

$userId = $_SESSION['login_user_id'];

$query = "SELECT COUNT(*) as new_notifications FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$newNotifications = $result->fetch_assoc()['new_notifications'];

echo json_encode(['hasNew' => $newNotifications > 0]);

$stmt->close();
$conn->close();
?>
