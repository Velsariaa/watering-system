<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "new_user";

$connection = new mysqli($servername, $username, $password, $dbname);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); 
    $stmt = $connection->prepare("SELECT image FROM plant_images WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($imageData);

    if ($stmt->fetch()) {
        header("Content-Type: image/jpeg"); 
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