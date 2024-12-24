<?php
// Test if we can write to the current directory
$testWrite = file_put_contents('debug.log', "Initial test: " . date('Y-m-d H:i:s') . "\n");
if ($testWrite === false) {
    error_log("Cannot write to debug.log in " . __DIR__);
}

$uri = '/' . ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Use error_log as a backup logging method
error_log("Processing URI: $uri");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Routing
switch ($uri) {
    case '/':
        file_put_contents('debug.log', "Redirecting to: pages/capslogin.php\n", FILE_APPEND);
        header("Location: pages/capslogin.php");
        exit;
    case '/login':
        file_put_contents('debug.log', "Including: pages/capslogin.php\n", FILE_APPEND);
        include 'pages/capslogin.php';
        break;
    case '/dashboard':
        file_put_contents('debug.log', "Including: pages/dashboard.php\n", FILE_APPEND);
        include 'pages/dashboard.php';
        break;
    case '/register':
        file_put_contents('debug.log', "Including: pages/register.php\n", FILE_APPEND);
        include 'pages/register.php';
        break;
    case '/plantdata':
        file_put_contents('debug.log', "Including: pages/plantdata.php\n", FILE_APPEND);
        include 'pages/plantdata.php';
        break;
    default:
        file_put_contents('debug.log', "404 Not Found for: $uri\n", FILE_APPEND);
        http_response_code(404);
        echo "404 Not Found";
        break;
}
