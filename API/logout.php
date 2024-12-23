<?php
session_start();
session_destroy(); // Destroy the session
header("Location: pages/capslogin.php"); // Redirect
exit;
?>
