<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "new_user";

// Create connection
$connection = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Retrieve the image
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize input
    $stmt = $connection->prepare("SELECT image FROM plant_images WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($imageData);

    if ($stmt->fetch()) {
        header("Content-Type: image/jpeg"); // Adjust for your image type
        echo $imageData;
    } else {
        echo "Image not found.";
    }

    $stmt->close();
} else {
    echo "No image ID provided.";
}

$connection->close();
?>