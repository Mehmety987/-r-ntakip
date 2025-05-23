<?php
// Database configuration
$host = 'localhost'; // Your database host (usually localhost)
$username = 'root';  // Your database username (replace with your actual username)
$password = '';      // Your database password (replace with your actual password)
$dbname = 'takipproje'; // Database name

// Create a connection to the database
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Uncomment the following line to test the connection
// echo "Connected successfully"; 
?>
