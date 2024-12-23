<?php
session_start();
session_destroy(); // Destroy the session
header("Location: capslogin.php"); // Redirect to login page
exit;
?>
