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

$createUsersTable = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)";

if (!$conn->query($createUsersTable)) {
    die("Error creating users table: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'plant_images') {
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (isset($inputData['plant_name'], $inputData['image'])) {
        $plantName = $inputData['plant_name'];
        $imageBase64 = $inputData['image']; // Expecting image as base64 string
        $image = base64_decode($imageBase64); // Decode base64 image

        // Save the image temporarily for Python script processing
        $tempImagePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid("plant_", true) . ".jpg";
        file_put_contents($tempImagePath, $image);

        // Execute Python script
        $pythonScriptPath = "../scripts/process_image.py"; // Adjust to your Python script's location
        $command = escapeshellcmd("python3 $pythonScriptPath $tempImagePath");
        $output = shell_exec($command);

        if ($output === null) {
            echo json_encode(["error" => "Failed to execute Python script."]);
            unlink($tempImagePath); // Clean up temporary file
            exit;
        }

        $result = json_decode($output, true);
        if (isset($result['error'])) {
            echo json_encode(["error" => $result['error']]);
            unlink($tempImagePath); // Clean up temporary file
            exit;
        }

        // Extract height and width from Python script output
        $height = $result['height'] ?? null;
        $width = $result['width'] ?? null;

        // Insert into plant_images table
        $stmt = $conn->prepare("INSERT INTO plant_images (plant_name, image, height, width) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sbdd", $plantName, $null, $height, $width);
        $stmt->send_long_data(1, $image);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Data successfully uploaded to plant_images table."]);
        } else {
            echo json_encode(["error" => "Failed to insert into plant_images: " . $stmt->error]);
        }

        $stmt->close();
        unlink($tempImagePath); // Clean up temporary file
    } else {
        echo json_encode(["error" => "Invalid input. Required fields: plant_name, image."]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'plant_watered_logs') {
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (isset($inputData['plant_name'], $inputData['datetime_watered'])) {
        $plantName = $inputData['plant_name'];
        $datetimeWatered = $inputData['datetime_watered'];

        // Insert into plant_watered_logs table
        $stmt = $conn->prepare("INSERT INTO plant_watered_logs (plant_name, datetime_watered) VALUES (?, ?)");
        $stmt->bind_param("ss", $plantName, $datetimeWatered);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Data successfully uploaded to plant_watered_logs table."]);
        } else {
            echo json_encode(["error" => "Failed to insert into plant_watered_logs: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Invalid input. Required fields: plant_name, datetime_watered."]);
    }
} else {
    echo json_encode(["error" => "Invalid request. Use 'action=plant_images' or 'action=plant_watered_logs' in the query string."]);
}

$conn->close();
?>
