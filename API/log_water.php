<?php

$servername = "localhost";
$username = "root";       
$password = "";            
$dbname = "new_user"; 

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$plantName = $_POST['name']; 
$datetime_watered = date('Y-m-d H:i:s');

$sql = "INSERT INTO plant_watered_logs (plant_name, datetime_watered) VALUES ('$plantName', '$datetime_watered')";

if ($conn->query($sql) === TRUE) {
    echo "Log inserted successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
