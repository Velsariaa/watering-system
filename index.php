<?php
$uri = '/' . ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

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

// Create tables if they do not exist
$createPlantImagesTable = "CREATE TABLE IF NOT EXISTS plant_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plant_name VARCHAR(255) DEFAULT NULL,
    image LONGBLOB NOT NULL,
    height FLOAT DEFAULT NULL,
    width FLOAT DEFAULT NULL,
    datetime_captured DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($createPlantImagesTable)) {
    die("Error creating or updating plant_images table: " . $conn->error);
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

$conn->close();

switch ($uri) {
    case '/':
        header("Location: /login");
        exit;
    case '/login':
        include 'pages/capslogin.php';
        break;
    case '/dashboard':
        include 'pages/dashboard.php';
        break;
    case '/register':
        include 'pages/register.php';
        break;
    case '/plantdata':
        include 'pages/plantdata.php';
        break;
    case '/api/log_water':
        include 'API/log_water.php';
        break;
    case '/api/upload':
        include 'API/upload.php';
        break;
    case '/api/display':
        include 'API/display.php';
        break;
    case '/api/update_plant_name':
        include 'API/update_plant_name.php';
        break;
    case '/delete_log': 
        include 'pages/delete_log.php';
        break;
    case '/delete_plant': // Added routing for delete_plant.php
        include 'pages/delete_plant.php';
        break;
    case '/logout':
        include 'API/logout.php';
        break;
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
?>
