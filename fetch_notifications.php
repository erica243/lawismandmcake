<?php
session_start();
include('db_connect.php');

$userId = $_SESSION['login_user_id']; // Get the logged-in user ID
$query = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
$query->bind_param("i", $userId);
$query->execute();
$result = $query->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$query->close();
z
echo json_encode(['status' => 'success', 'notifications' => $notifications]);
?>
