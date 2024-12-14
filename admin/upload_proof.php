<?php
// Start session and include database connection if necessary
session_start();
include 'db_connect.php'; // Ensure this file connects to your database properly

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the file and order ID are set
    if (isset($_FILES['proof_of_delivery']) && isset($_POST['order_id'])) {
        // Retrieve the order ID
        $orderId = intval($_POST['order_id']); // Sanitize input

        // Define the target directory
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true); // Create directory if it doesn't exist
        }

        // Get file information
        $fileName = basename($_FILES["proof_of_delivery"]["name"]);
        $fileTmpPath = $_FILES["proof_of_delivery"]["tmp_name"];
        $fileSize = $_FILES["proof_of_delivery"]["size"];
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
        $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf']; // Define allowed file types

        // Generate a unique file name to prevent overwriting
        $newFileName = uniqid('proof_', true) . "." . $fileType;
        $targetFilePath = $targetDir . $newFileName;

        // Validate the file
        if (!in_array(strtolower($fileType), $allowedTypes)) {
            echo "Invalid file type. Allowed types: " . implode(', ', $allowedTypes);
            exit;
        }

        if ($fileSize > 5 * 1024 * 1024) { // Limit file size to 5MB
            echo "File size exceeds the limit of 5MB.";
            exit;
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
            // Update the database with the uploaded file name
            $stmt = $conn->prepare("UPDATE orders SET proof_of_delivery = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("si", $newFileName, $orderId);
                if ($stmt->execute()) {
                    echo "success";
                } else {
                    echo "Database update failed: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "Failed to prepare database statement.";
            }
        } else {
            echo "Failed to upload file.";
        }
    } else {
        echo "File or Order ID missing.";
    }
} else {
    echo "Invalid request method.";
}
