<?php

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

// Connection successful
?>