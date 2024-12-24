<?php

session_start();
include './api/db.php'; // Correct the path to the database connection file

// Ensure $pdo is defined
if (!isset($pdo)) {
    die("Database connection failed.");
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate form inputs
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Hash the password before saving it to the database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if the username already exists in the database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            $error = "Username already taken!";
        } else {
            // Insert the new user into the database
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();

            // Redirect to the login page after successful registration
            $_SESSION['username'] = $username;
            header("Location: /login");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #e8f5e9; /* Soft green background */
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh; /* Full-screen height */
    color: #2e7d32; /* Dark green text */
}

h2 {
    text-align: center;
    margin-bottom: 20px;
    margin-right: 10%;
    color: #1b5e20; /* Darker green for headings */
}

form {
    width: 400px;
    padding: 20px;
    background-color: #ffffff; /* White form background */
    border-radius: 15px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    border: 6px solid; /* Thick border with gradient */
    border-image: linear-gradient(to right, #66bb6a, #43a047); /* Green gradient */
    border-image-slice: 1; /* Ensures the full gradient is used */
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

input[type="text"], input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #c8e6c9; /* Light green border */
    border-radius: 5px;
    box-sizing: border-box;
    font-size: 14px;
}

input[type="text"]:focus, input[type="password"]:focus {
    border-color: #66bb6a; /* Highlight border on focus */
    outline: none;
    box-shadow: 0 0 5px rgba(102, 187, 106, 0.5); /* Soft green glow */
}

button {
    width: 100%;
    padding: 10px;
    background-color: #2e7d32; /* Dark green button */
    color: #ffffff; /* White text */
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #1b5e20; /* Darker green on hover */
}

p {
    text-align: center;
    margin-top: 10px;
}

p a {
    color: #2e7d32;
    text-decoration: none;
    font-weight: bold;
}

p a:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>
    <h2>Create an Account</h2>
    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br><br>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required><br><br>

        <button type="submit">Register</button>
    </form>

    <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
</body>
</html>
