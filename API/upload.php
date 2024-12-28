<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "new_user";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (isset($inputData['image'])) {
        $plantName = $inputData['plant_name'] ?? null; 
        $imageBase64 = $inputData['image']; 
        $image = base64_decode($imageBase64); 

        
        $tempImagePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid("plant_", true) . ".jpg";
        file_put_contents($tempImagePath, $image);

 
        $pythonScriptPath = "C:/Users/Zyd/Desktop/watering-system/scripts/process_image.py"; 
        $command = escapeshellcmd("python3 $pythonScriptPath $tempImagePath");
        $output = shell_exec($command);

        if ($output === null) {
            echo json_encode(["error" => "Failed to execute Python script."]);
            unlink($tempImagePath);
            exit;
        }

        $result = json_decode($output, true);
        if (isset($result['error'])) {
            echo json_encode(["error" => $result['error']]);
            unlink($tempImagePath); 
            exit;
        }

    
        $height = $result['height'] ?? null;
        $width = $result['width'] ?? null;

       
        $stmt = $conn->prepare("INSERT INTO plant_images (plant_name, image, height, width) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sbdd", $plantName, $null, $height, $width);
        $stmt->send_long_data(1, $image);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Data successfully uploaded to plant_images table."]);
        } else {
            echo json_encode(["error" => "Failed to insert into plant_images: " . $stmt->error]);
        }

        $stmt->close();
        unlink($tempImagePath); 
    } else {
        echo json_encode(["error" => "Invalid input. Required field: image."]);
    }
}

$conn->close();
?>
