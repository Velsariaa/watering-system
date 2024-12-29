<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: /login"); 
    exit;
}

if (isset($_GET['plant_name']) && isset($_GET['datetime_watered'])) {
    $plantName = $_GET['plant_name'];
    $datetimeWatered = $_GET['datetime_watered'];

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "new_user";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "DELETE FROM plant_watered_logs WHERE plant_name = ? AND datetime_watered = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $plantName, $datetimeWatered);

    if ($stmt->execute() === TRUE) {
        echo "Log deleted successfully!";
    } else {
        echo "Error deleting log: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    header("Location: plantdata");
    exit;
} else {
    echo "Invalid request.";
}
?>
