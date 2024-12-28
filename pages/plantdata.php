<?php
session_start();
$uploaded = false;
$processedFile = "";


$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "new_user";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (!isset($_SESSION['username'])) {
    header("Location: pages/capslogin.php"); 
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    
    $plantName = !empty($_POST['plant_name']) ? $_POST['plant_name'] : "Plant " . time();

    
    $imageName = $_FILES['image']['name'];
    $imageTmpName = $_FILES['image']['tmp_name'];
    $imageSize = $_FILES['image']['size'];
    $imageError = $_FILES['image']['error'];

    
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true); 
    }
    
    $uniqueImageName = time() . "_" . basename($imageName);
    $targetFile = $targetDir . $uniqueImageName;

    
    $allowedTypes = array('jpg', 'jpeg', 'png');
    $imageExtension = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    
    if (in_array($imageExtension, $allowedTypes)) {
        if ($imageError === 0) {
            if ($imageSize < 5000000) {
                if (move_uploaded_file($imageTmpName, $targetFile)) {
                    $uploaded = true;

                    
                    switch ($imageExtension) {
                        case 'jpg':
                        case 'jpeg':
                            $image = imagecreatefromjpeg($targetFile);
                            break;
                        case 'png':
                            $image = imagecreatefrompng($targetFile);
                            break;
                        default:
                            echo "Unsupported image format.";
                            exit;
                    }

                    if (!$image) {
                        echo "Failed to process the image.";
                        exit;
                    }

                    
                    $imageWidth = imagesx($image);
                    $imageHeight = imagesy($image);

                    
                    $minX = $imageWidth;
                    $minY = $imageHeight;
                    $maxX = 0;
                    $maxY = 0;

                    
                    for ($y = 0; $y < $imageHeight; $y++) {
                        for ($x = 0; $x < $imageWidth; $x++) {
                            $rgb = imagecolorat($image, $x, $y);
                            $r = ($rgb >> 16) & 0xFF;
                            $g = ($rgb >> 8) & 0xFF;
                            $b = $rgb & 0xFF;

                            
                            if ($r < 120 && $g > 100 && $b < 100) {
                                
                                if ($x < $minX) $minX = $x;
                                if ($x > $maxX) $maxX = $x;
                                if ($y < $minY) $minY = $y;
                                if ($y > $maxY) $maxY = $y;
                            }
                        }
                    }

                    
                    $plantWidth = $maxX - $minX;
                    $plantHeight = $maxY - $minY;

                    
                    $red = imagecolorallocate($image, 255, 0, 0);
                    imagerectangle($image, $minX, $minY, $maxX, $maxY, $red);

                    
                    $processedFile = 'uploads/processed_' . $uniqueImageName;
                    imagejpeg($image, $processedFile);
                    imagedestroy($image);

                   
                    $sql = "INSERT INTO plant_data (plant_name, image_filename, width, height)
                            VALUES ('$plantName', '$uniqueImageName', $plantWidth, $plantHeight)";
                    if ($conn->query($sql) === TRUE) {
                        echo "Plant data saved successfully!";
                    } else {
                        echo "Error: " . $sql . "<br>" . $conn->error;
                    }

                    
                    $sql_log = "UPDATE plant_watered_logs SET plant_name = '$plantName'";
                    if ($conn->query($sql_log) !== TRUE) {
                        echo "Error updating plant_watered_logs: " . $conn->error;
                    }
                } else {
                    echo "Sorry, there was an error uploading your file.";
                }
            } else {
                echo "Sorry, your file is too large.";
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "Sorry, only JPG, JPEG, and PNG files are allowed.";
    }
}


$sql = "SELECT * FROM plant_images";
$result = $conn->query($sql);


$sql_logs = "SELECT plant_name, datetime_watered FROM plant_watered_logs ORDER BY datetime_watered DESC";
$result_logs = $conn->query($sql_logs);

$conn->close(); 
?>


<body>
 <style>
 * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: Arial, sans-serif;
        background-image: linear-gradient(white, gray);
        margin: 0;
    }
    
    nav {
        width: 350px;
        background-color: #19461A;
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        padding-top: 20px;
    }
    nav ul {
        list-style-type: none;
        padding: 0;
    }
    nav ul li {
        width: 100%;
    }
    nav ul li a {
        display: block;
        color: white;
        text-align: center;
        padding: 16px 20px;
        text-decoration: none;
        font-size: 16px;
    }
    nav ul li a:hover {
        background-color: pink;
        color: black;
    }

    
    .content {
        margin-left: 220px; 
        padding: 40px;
        text-align: center;
    }

    .content h1 {
        color: #333;
    }

    
    table {
        width: 80%;
        margin: 20px auto;
        border-collapse: collapse;
    }
    th, td {
        padding: 12px;
        text-align: center;
        border: 1px solid #ddd;
    }
    th {
        background-color: #4CAF50;
        color: white;
    }
    tr:nth-child(even) {
        background-color:rgb(175, 175, 175);
    }


    footer {
        background-color: #333;
        color: white;
        text-align: center;
        padding: 10px;
        position: fixed;
        width: 100%;
        bottom: 0;
    }

   
    @media screen and (max-width: 600px) {
        nav {
            width: 100%;
            height: auto;
            position: relative;
        }

        nav ul {
            display: flex;
            flex-direction: column;
        }

        nav ul li {
            width: 100%;
            text-align: left;
        }

        .content {
            margin-left: 0;
            padding: 20px;
        }
    }

    </style>
  <nav>
        <ul>
            <li><a href="/dashboard">Dashboard</a></li>
            <li><a href="/configure-esp32">Configure ESP32 Cam</a></li>
            <li><a href="/plantdata">View Plant Data</a></li>
            <li><a href="./api/logout">Logout</a></li>
        </ul>
    </nav>

    <div class="content">
        <h1>WELCOME,<br> to BSIT 4A Plantito's and Plantita's <br><?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <hr>
    <div class="content">
        <h2>Upload an Image to Measure Plant Dimensions</h2>
        <form id="plantForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
            <label for="plant_name">Plant Name:</label>
            <input type="text" name="plant_name" id="plant_name" placeholder="Enter plant name" required readonly><br><br>
            <button type="button" id="editButton" onclick="editPlantName()">Edit</button>
            <button type="button" id="saveButton" onclick="savePlantName()" style="display:none;">Save Changes</button><br><br>
            <label for="image">Choose an image:</label>
            <input type="file" name="image" id="image" accept="image/*" required><br><br>
            <input type="submit" value="Upload Image">
        </form>

        <h3>Existing Plant Data:</h3>
        <table>
            <thead>
                <tr>
                    <th>Plant Name</th>
                    <th>Plant Image</th>
                    <th>Width (px)</th>
                    <th>Height (px)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['plant_name']); ?></td>
                            <td><img src="../API/display_image.php?id=<?php echo $row['id']; ?>" alt="Plant Image" style="max-width: 100px; height: auto;"></td>
                            <td><?php echo $row['width']; ?></td>
                            <td><?php echo $row['height']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No plant data found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h3>Plant Watered Logs:</h3>
        <table>
            <thead>
                <tr>
                    <th>Plant Name</th>
                    <th>Date and Time Watered</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_logs->num_rows > 0): ?>
                    <?php while ($row = $result_logs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['plant_name']); ?></td>
                            <td><?php echo $row['datetime_watered']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">No watering logs found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function editPlantName() {
            document.getElementById('plant_name').readOnly = false;
            document.getElementById('editButton').style.display = 'none';
            document.getElementById('saveButton').style.display = 'inline';
        }

        function savePlantName() {
            document.getElementById('plant_name').readOnly = true;
            document.getElementById('editButton').style.display = 'inline';
            document.getElementById('saveButton').style.display = 'none';

        
            var plantName = document.getElementById('plant_name').value;
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "./api/update_plant_name.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        alert(xhr.responseText); 
                        location.reload(); 
                    } else {
                        alert('Error: ' + xhr.status);
                    }
                }
            };
            xhr.send("plant_name=" + encodeURIComponent(plantName));
        }
    </script>
</body>
</html>