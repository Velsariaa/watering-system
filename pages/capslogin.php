<?php
session_start();
include '/api/db.php'; // Include the database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to find the user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch();

    // Check if the user exists and if the password is correct
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $username;
        header("Location: pages/dashboard.php"); // Redirect to the dashboard if login is successful
        exit;
    } else {
        $error = "Invalid username or password"; // Display error message if login fails
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body Styling */
        body {
            font-family: Arial, sans-serif;
            background-image: url(/assets/123.jpg);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        background-repeat: no-repeat, no-repeat;
        background-size: 100% 100%;
        }

        /* Form Container */
        .form-container {
            background-image: linear-gradient(#3AA346,#3AA346);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        /* Form Elements */
        label {
            display: block;
            margin-bottom: 5px;
            font-size: 16px;
            color: black;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            font-size: 16px;
            border-radius: 25px; /* Makes the input oval */
            border: 1px solid #ccc;
            outline: none;
            box-sizing: border-box;
            background-color: #f9f9f9;
        }

        input:focus {
            border-color: blue;
        }

        button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            background-color: blue;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Optional - style for the error message */
        .error-message {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }

        /* Register link */
        a {
            text-decoration: none;
            color:yellow;
        }

        a:hover {
            text-decoration: underline;
        }
img{
    width: 180px;
    height: 180px;
    border-radius: 50%;
    box-sizing: border-box;
            background-color: #f9f9f9;
            border: 4px solid transparent; /* Transparent border for gradient effect */
            background-image: linear-gradient(to right, green, blue), linear-gradient(to right, yellow, red); /* Dual color gradient */
            background-origin: border-box;
            background-clip: content-box, border-box
}

    </style>
</head>
<body>

    <div class="form-container">
        
<center><img src="/assets/picss.jpg" alt="plant1" class="im1"></center>

        <h2 style="text-align: center;">LOG-IN HERE!</h2>
        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" placeholder="Enter your username" required><br>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" placeholder="Enter your password" required><br>

            <button type="submit">Login</button>
            <br>
        </form>
        <br>

        <!-- Display error if exists -->
        <?php if (isset($error)) { echo "<p class='error-message'>$error</p>"; } ?>
<br>
        <p style="text-align: center;">Don't have an account? <br><a href="register.php" >Register HERE</a></p>
    </div>

</body>
</html>