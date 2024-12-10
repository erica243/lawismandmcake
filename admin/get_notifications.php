<?php
// Include necessary files
include 'admin_class.php';
$crud = new Action();

if (isset($_GET['action']) && $_GET['action'] == "get_notifications") {
    // Create database connection
    $conn = new mysqli('localhost', 'root', '', 'db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to get the count of orders with status 0
    $result = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE status = 0");
    if ($result) {
        $data = $result->fetch_assoc();
        echo $data['count'];
    } else {
        echo "0";
    }

    $conn->close();
}
?>
