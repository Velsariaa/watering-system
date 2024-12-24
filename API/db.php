<?php
$host = 'localhost';
$dbname = 'new_user'; // Ensure you have this database created
$username = 'root'; // Your MySQL username
$password = ''; // Your MySQL password (leave blank if using the default)

try {
    // Create a PDO instance for database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Error handling
} catch (PDOException $e) {
    die("Could not connect to the database $dbname: " . $e->getMessage());
}
?>
