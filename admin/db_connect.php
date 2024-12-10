<?php

$servername = '127.0.0.1'; // Database server address
$username = 'u510162695_fos_db'; // Database username
$password = '1Fos_db_password'; // Database password
$dbname = 'u510162695_fos_db'; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to select all data from user_info table
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Start HTML table
    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr>";
    
    // Display table headers dynamically
    while ($fieldinfo = $result->fetch_field()) {
        echo "<th>" . htmlspecialchars($fieldinfo->name) . "</th>";
    }
    echo "</tr>";

    // Display table rows dynamically
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No data found in the user_info table.";
}

// Close the connection
$conn->close();

?>
