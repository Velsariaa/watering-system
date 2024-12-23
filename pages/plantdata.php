<?php
session_start();
$uploaded = false;
$processedFile = "";

// Database connection
$servername = "localhost";
$username = "root"; // default MySQL username in XAMPP
$password = ""; // default MySQL password in XAMPP
$dbname = "new_user";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: capslogin.php"); // Redirect to login page if not logged in
    exit;
}

// Handle the image upload and processing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    // Get form inputs
    $plantName = !empty($_POST['plant_name']) ? $_POST['plant_name'] : "Plant " . time();

    // Get image info
    $imageName = $_FILES['image']['name'];
    $imageTmpName = $_FILES['image']['tmp_name'];
    $imageSize = $_FILES['image']['size'];
    $imageError = $_FILES['image']['error'];

    // Define target directory and file path
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true); // Create uploads directory if it doesn't exist
    }
    // Add timestamp to make the filename unique
    $uniqueImageName = time() . "_" . basename($imageName);
    $targetFile = $targetDir . $uniqueImageName;

    // Allowed file types
    $allowedTypes = array('jpg', 'jpeg', 'png');
    $imageExtension = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if the file is a valid image type
    if (in_array($imageExtension, $allowedTypes)) {
        if ($imageError === 0) {
            if ($imageSize < 5000000) {
                if (move_uploaded_file($imageTmpName, $targetFile)) {
                    $uploaded = true;

                    // Load the image dynamically
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

                    // Get image dimensions
                    $imageWidth = imagesx($image);
                    $imageHeight = imagesy($image);

                    // Plant-specific processing
                    $minX = $imageWidth;
                    $minY = $imageHeight;
                    $maxX = 0;
                    $maxY = 0;

                    // Loop through pixels to find plant area (green tones)
                    for ($y = 0; $y < $imageHeight; $y++) {
                        for ($x = 0; $x < $imageWidth; $x++) {
                            $rgb = imagecolorat($image, $x, $y);
                            $r = ($rgb >> 16) & 0xFF;
                            $g = ($rgb >> 8) & 0xFF;
                            $b = $rgb & 0xFF;

                            // Detect green tones (plant-specific range)
                            if ($r < 120 && $g > 100 && $b < 100) {
                                // Update the bounding box
                                if ($x < $minX) $minX = $x;
                                if ($x > $maxX) $maxX = $x;
                                if ($y < $minY) $minY = $y;
                                if ($y > $maxY) $maxY = $y;
                            }
                        }
                    }

                    // Calculate plant dimensions
                    $plantWidth = $maxX - $minX;
                    $plantHeight = $maxY - $minY;

                    // Optionally, highlight the plant region
                    $red = imagecolorallocate($image, 255, 0, 0);
                    imagerectangle($image, $minX, $minY, $maxX, $maxY, $red);

                    // Save the processed image
                    $processedFile = 'uploads/processed_' . $uniqueImageName;
                    imagejpeg($image, $processedFile);
                    imagedestroy($image);

                    // Save the plant data to MySQL
                    $sql = "INSERT INTO plant_data (plant_name, image_filename, width, height)
                            VALUES ('$plantName', '$uniqueImageName', $plantWidth, $plantHeight)";
                    if ($conn->query($sql) === TRUE) {
                        echo "Plant data saved successfully!";
                    } else {
                        echo "Error: " . $sql . "<br>" . $conn->error;
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

// Fetch existing plant data from the database
$sql = "SELECT * FROM plant_data";
$result = $conn->query($sql);

$conn->close(); // Close the connection
?>

<!-- HTML content -->
<body>
 <style>
 * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    /* Body Styling */
    body {
        font-family: Arial, sans-serif;
        background-image: linear-gradient(white, gray);
        margin: 0;
    }
    /* Navigation Bar Styling */
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

    /* Main Content Styling */
    .content {
        margin-left: 220px; /* Adjust according to navbar width */
        padding: 40px;
        text-align: center;
    }

    .content h1 {
        color: #333;
    }

    /* Table Styling */
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
        background-color: #f2f2f2;
    }

    /* Footer Styling */
    footer {
        background-color: #333;
        color: white;
        text-align: center;
        padding: 10px;
        position: fixed;
        width: 100%;
        bottom: 0;
    }

    /* Responsive Design for Mobile */
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
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="configure-esp32.html">Configure ESP32 Cam</a></li>
            <li><a href="plantdata.php">View Plant Data</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="content">
        <h1>WELCOME,<br> to BSIT 4A Plantito's and Plantita's <br><?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <hr>
    <div class="content">
        <h2>Upload an Image to Measure Plant Dimensions</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
            <label for="plant_name">Plant Name:</label>
            <input type="text" name="plant_name" id="plant_name" placeholder="Enter plant name" required><br><br>
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
                            <td><img src="uploads/<?php echo htmlspecialchars($row['image_filename']); ?>" alt="Plant Image" style="max-width: 100px; height: auto;"></td>
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
    </div>
</body>
</html>