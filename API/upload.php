<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "new_user";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$createDatabase = "CREATE DATABASE IF NOT EXISTS $dbname";
if (!$conn->query($createDatabase)) {
    die("Error creating database: " . $conn->error);
}

$conn->select_db($dbname);

$createPlantImagesTable = "CREATE TABLE IF NOT EXISTS plant_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plant_name VARCHAR(255) NOT NULL,
    image LONGBLOB NOT NULL,
    height FLOAT DEFAULT NULL,
    width FLOAT DEFAULT NULL,
    datetime_captured DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($createPlantImagesTable)) {
    die("Error creating plant_images table: " . $conn->error);
}

$createPlantWateredLogsTable = "CREATE TABLE IF NOT EXISTS plant_watered_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plant_name VARCHAR(255) NOT NULL,
    datetime_watered DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($createPlantWateredLogsTable)) {
    die("Error creating plant_watered_logs table: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'plant_images') {
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (isset($inputData['plant_name'], $inputData['image'])) {
        $plantName = $inputData['plant_name'];
        $image = base64_decode($inputData['image']); // Expecting image as base64 string
        $height = isset($inputData['height']) ? (float)$inputData['height'] : null;
        $width = isset($inputData['width']) ? (float)$inputData['width'] : null;

        // Insert into plant_images table
        $stmt1 = $conn->prepare("INSERT INTO plant_images (plant_name, image, height, width) VALUES (?, ?, ?, ?)");
        $stmt1->bind_param("sbdd", $plantName, $null, $height, $width);
        $stmt1->send_long_data(1, $image);

        if ($stmt1->execute()) {
            echo json_encode(["message" => "Data successfully uploaded to plant_images table."]);
        } else {
            echo json_encode(["error" => "Failed to insert into plant_images: " . $stmt1->error]);
        }

        $stmt1->close();
    } else {
        echo json_encode(["error" => "Invalid input. Required fields: plant_name, image."]);
    }
}

elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'plant_watered_logs') {
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (isset($inputData['plant_name'], $inputData['datetime_watered'])) {
        $plantName = $inputData['plant_name'];
        $datetimeWatered = $inputData['datetime_watered'];

        // Insert into plant_watered_logs table
        $stmt2 = $conn->prepare("INSERT INTO plant_watered_logs (plant_name, datetime_watered) VALUES (?, ?)");
        $stmt2->bind_param("ss", $plantName, $datetimeWatered);

        if ($stmt2->execute()) {
            echo json_encode(["message" => "Data successfully uploaded to plant_watered_logs table."]);
        } else {
            echo json_encode(["error" => "Failed to insert into plant_watered_logs: " . $stmt2->error]);
        }

        $stmt2->close();
    } else {
        echo json_encode(["error" => "Invalid input. Required fields: plant_name, datetime_watered."]);
    }
}

else {
    echo json_encode(["error" => "Invalid request. Use 'action=plant_images' or 'action=plant_watered_logs' in the query string."]);
}

$conn->close();
?>
