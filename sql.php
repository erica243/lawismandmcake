<?php

// Database connection details
$servername = '127.0.0.1'; 
$username = 'u510162695_fos_db'; 
$password = '1Fos_db_password'; 
$dbname = 'u510162695_fos_db'; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Modify the table to add recaptcha_token
$alter_sql = "ALTER TABLE user_info ADD recaptcha_token VARCHAR(255) NOT NULL AFTER password";
if ($conn->query($alter_sql) === TRUE) {
    echo "Column recaptcha_token added successfully.<br>";
} else {
    echo "Error adding column: " . $conn->error . "<br>";
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
