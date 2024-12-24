<?php
$uri = '/' . ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');


switch ($uri) {
    case '/':
        header("Location: pages/capslogin.php");
        exit;
    case '/login':
        include 'pages/capslogin.php';
        break;
    case '/dashboard':
        include 'pages/dashboard.php';
        break;
    case '/register':
        include 'pages/register.php';
        break;
    case '/plantdata':
        include 'pages/plantdata.php';
        break;
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
