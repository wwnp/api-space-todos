<?php

declare(strict_types=1);

require dirname(__DIR__) . "/html/vendor/autoload.php";

header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY");
header("Content-type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Max-Age: 86400");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__ . "/api-in-php-udemy"));
$dotenv->load();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', $path);
$resource = $parts[1];
$id = $parts[2] ?? null;

if ($resource != 'todos') {
    http_response_code(404);
    exit;
}
$database = new Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PASS"]);

$userGateway = new UserGateway($database);
$auth = new Auth($userGateway);
if (!$auth->authenticateAPIKey()) {
    exit;
}
$userId = $auth->getUserID();

$task = new Task($database);

$controller = new TaskController($Task, $userId);
$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);
