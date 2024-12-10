<?php
include 'db_connect.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shipping_amounts = $_POST['shipping_amount'];

    foreach ($shipping_amounts as $address => $amount) {
        // Sanitize input
        $address = $conn->real_escape_string($address);
        $amount = floatval($amount); // Ensure the amount is treated as a float

        // Insert or update the shipping amount in your database
        $query = "INSERT INTO shipping_info (address, shipping_amount) 
                  VALUES ('$address', $amount) 
                  ON DUPLICATE KEY UPDATE shipping_amount = $amount"; 

        if ($conn->query($query) !== TRUE) {
            echo "Error updating shipping amount for address: $address. " . $conn->error . "<br>";
        }
    }

    // Redirect back to the form with a success message after the entire operation
    header("Location: shipping.php?success=1");
    exit(); // Always use exit after a redirect to stop further script execution
}

$conn->close();
?>
