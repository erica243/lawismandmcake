<?php

// Database connection details
$servername = '127.0.0.1'; // Typically 'localhost' or '127.0.0.1' for local servers
$username = 'u510162695_fos_db'; // Your database username
$password = '1Fos_db_password'; // Your database password
$dbname = 'u510162695_fos_db'; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to describe the table
$table_name = 'user_info';
$sql = "DESCRIBE $table_name";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

    // Output data for each row
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No data found for the table description.";
}

// Close connection
$conn->close();

?>
