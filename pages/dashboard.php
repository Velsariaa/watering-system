<?php
session_start();

// Check if the user is logged in
// if (!isset($_SESSION['username'])) {
//     header("Location: /login"); 
//     exit;
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
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
        background-image: linear-gradient(white,gray);
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
        text-align: center; /* Center the welcome message */
    }

    .content h1 {
        color: #333;
    }

    /* Footer Styling */
    footer {
        background-color: #333;
        color: white;
        text-align: center;
        padding: 2px;
        position: fixed;
        width: 100%;
        bottom: 0;
    }
    pre{
        margin-left: 180px;
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

  .im{
    width: 400px;
    height: 150px;
  }
  P{
    text-align: center;
  }
    </style>

    <nav>
        <ul>
            <li><a href="dashboard">HOME PAGE</a></li>
            <li><a href="plantdata">PLANT IMAGE</a></li>
            <li><a href="logout">LOG OUT</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="content">
        <h1>WELCOME,<br> to BSIT 4A Plantito's and Plantita's <br><?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <hr>
        <br>
        <br>
        <p>
 
    <img src="/assets/picss.jpg" alt="plant2" class="im">
<br>
<p>
<h1>Mission</h1><br>
To provide high-quality, sustainable, and beautifully curated plants<br>
and gardening essentials that enhance living spaces while fostering <br>
a deeper connection to nature.
</p>
<p>
<h1>Vision</h1><br>
To be the leading plant shop, inspiring a greener, <br>
healthier, and more eco-conscious community by promoting the love <br>
and care for plants.
</p>  
</div>

    </div>
   
    <!-- Footer -->
    <footer>
        <pre>&copy; 2024 Plant Monitoring System. All Rights Reserved.</pre>
    </footer>
</body>
</html>
