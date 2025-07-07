<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header("Access-Control-Allow-Credentials: true");

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
    http_response_code(200);
    exit();
}

require_once "core/Database.php";
require_once "core/Controller.php";
require_once "core/App.php";
require_once "controllers/AuthController.php";
require_once "controllers/UserController.php";
require_once "controllers/VendorController.php";

$app = new App();

$base = '/zedfair_backend';
$uri = str_replace($base, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$uri = rtrim($uri, '/');

if ($uri === '' || $uri === '/zedfair_backend') $uri = '/';

$publicRoutes = [
    '/',
    '/addvendor',
    '/signup',
    '/vendor/category',
    '/vendor/addvendor'
];

if (!isset($_SESSION['email']) && !in_array($uri, $publicRoutes)) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Authentication required',
        'message' => 'Please login first',
        'uri' => $uri
    ]);

    exit();
}


$app->loadConfig('database');
$app->loadConfig('app');

$app->run();

?>