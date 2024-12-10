<?php
include('db_connect.php');

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Prepare and execute the delete statement
    $stmt = $conn->prepare("DELETE FROM user_info WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        echo "User deleted successfully.";
    } else {
        echo "Error deleting user: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
