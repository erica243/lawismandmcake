<?php

$servername = '127.0.0.1'; // Remote server address
$username = 'u510162695_fos_db'; // Your database username
$password = '1Fos_db_password'; // Your database password
$dbname = 'u510162695_fos_db'; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch all rows from user_info table
$sql = "SELECT * FROM user_info";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h1>User Info Table</h1>";
    echo "<table border='1'>";
    echo "<tr>";
    // Fetch column names dynamically
    while ($fieldinfo = $result->fetch_field()) {
        echo "<th>" . htmlspecialchars($fieldinfo->name) . "</th>";
    }
    echo "</tr>";
    
    // Fetch and display rows
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
