<?php
$conn = new mysqli("localhost", "root", "", "new_user");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT plant_name, image, height, width, capture_time FROM plant_images";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'><tr><th>Plant Name</th><th>Image</th><th>Height</th><th>Width</th><th>Capture Time</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['plant_name'] . "</td>";
        echo "<td><img src='data:image/jpeg;base64," . base64_encode($row['image']) . "' height='100'></td>";
        echo "<td>" . $row['height'] . "</td>";
        echo "<td>" . $row['width'] . "</td>";
        echo "<td>" . $row['capture_time'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No data found";
}

$conn->close();
?>
