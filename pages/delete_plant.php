<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: /login"); 
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $plantId = intval($_GET['id']); // Ensure the id is an integer

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "new_user";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "DELETE FROM plant_images WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $plantId);

    if ($stmt->execute() === TRUE) {
        echo "Plant deleted successfully!";
    } else {
        echo "Error deleting plant: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    header("Location: plantdata");
    exit;
} else {
    echo "Invalid request.";
}
?>
