<?php
// Database configuration
$host = 'localhost'; // Hostname (usually localhost)
$username = 'root'; // Database username
$password = ''; // Database password
$database = 'e_market'; // Database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set the charset for the connection
$conn->set_charset("utf8")

?>
