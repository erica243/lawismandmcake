<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $orderId = $_POST['order_id'];

    // Use prepared statements for security
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $orderId);

    if ($stmt->execute()) {
        // Redirect to the order details page after deletion
        header("Location: order_details.php?id=$orderId&message=Order deleted successfully");
        exit(); // Make sure to exit after redirecting
    } else {
        // Handle the error
        echo "Error deleting order: " . $stmt->error;
    }
}
?>
