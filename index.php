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
    case '/api/log_water':
        include 'API/log_water.php';
        break;
    case '/api/upload':
        include 'API/upload.php';
        break;
    case '/api/display':
        include 'API/display.php';
        break;
    case '/api/update_plant_name':
        include 'API/update_plant_name.php';
        break;
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
?>
