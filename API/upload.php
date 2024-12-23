<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "new_user";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get image data
$image = file_get_contents('php://input');
$plantName = "Sample Plant"; // Optionally, send plant name from ESP32

// Save to database
$stmt = $conn->prepare("INSERT INTO plant_images (plant_name, image) VALUES (?, ?)");
$stmt->bind_param("sb", $plantName, $null);
$stmt->send_long_data(1, $image);

if ($stmt->execute()) {
    echo "Image uploaded and saved to database.";
} else {
    echo "Failed to upload image: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
