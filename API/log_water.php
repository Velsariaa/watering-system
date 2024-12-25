<?php
// Database connection
$servername = "localhost"; // Your database server
$username = "root";        // Your database username
$password = "";            // Your database password
$dbname = "new_user";  // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from ESP8266
$datetime_watered = date('Y-m-d H:i:s'); // Get the current timestamp

// Insert data into the table
$sql = "INSERT INTO plant_watered_logs (datetime_watered) VALUES ('$datetime_watered')";

if ($conn->query($sql) === TRUE) {
    echo "Log inserted successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
