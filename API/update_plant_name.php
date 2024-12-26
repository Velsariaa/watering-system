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
    $sql = "UPDATE plant_watered_logs SET plant_name = '$plantName'";
    if ($conn->query($sql) === TRUE) {
        echo "Plant name updated successfully!";
    } else {
        echo "Error updating plant name: " . $conn->error;
    }
}

$conn->close();
?>
