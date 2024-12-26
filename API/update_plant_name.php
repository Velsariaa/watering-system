<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "new_user";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $plantName = $_POST['plant_name'];

    // Update plant_name in plant_watered_logs
    $sql_logs = "UPDATE plant_watered_logs SET plant_name = '$plantName'";
    if ($conn->query($sql_logs) === TRUE) {
        echo "Plant name updated successfully in plant_watered_logs!";
    } else {
        echo "Error updating plant name in plant_watered_logs: " . $conn->error;
    }

    // Update plant_name in plant_images
    $sql_images = "UPDATE plant_images SET plant_name = '$plantName'";
    if ($conn->query($sql_images) === TRUE) {
        echo "Plant name updated successfully in plant_images!";
    } else {
        echo "Error updating plant name in plant_images: " . $conn->error;
    }
}

$conn->close();
?>
