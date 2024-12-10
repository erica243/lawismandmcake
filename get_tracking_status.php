

<?php
include('admin/db_connect.php');

$order_id = $_GET['order_id'];
$query = $conn->prepare("SELECT tracking_status FROM orders WHERE id = ?");
$query->bind_param("i", $order_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['tracking_status' => $row['tracking_status']]);
} else {
    echo json_encode(['tracking_status' => 'Unknown']);
}

$conn->close();
?>
