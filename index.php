<?php

declare(strict_types=1);

use App\ErrorHandler;
use App\Database;
use App\User;
use App\Auth;
use App\Image;
use App\ImageController;
use Aws\S3\S3Client;

require "./vendor/autoload.php";

$allowedOrigins = ["*"];

header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY");
header("Content-type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: " . implode(", ", $allowedOrigins)); // Set allowed origins
header("Access-Control-Allow-Methods: *");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Max-Age: 86400");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

set_error_handler([ErrorHandler::class, 'handleError']);
set_exception_handler([ErrorHandler::class, 'handleException']);

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__ . "/api-in-php-udemy"));
$dotenv->load();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', $path);
$resource = $parts[1];
$id = $parts[2] ?? null;

if ($resource != 'todos' && $resource != 'images') {
    http_response_code(404);
    exit;
}
$database = new Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PASS"]);

$user = new User($database);
$auth = new Auth($user);
if (!$auth->authenticateAPIKey()) {
    exit;
}
$userId = $auth->getUserID();


if ($resource === 'images') {
    $s3 = new S3Client([
        'version'     => 'latest',
        'endpoint' => 'https://storage.yandexcloud.net',
        'region'      => 'ru-central1',
        'credentials' => [
            'key'    => $_ENV['YANDEX_CLOUD_ACCESS_TOKEN'],
            'secret' => $_ENV['YANDEX_CLOUD_SECRET_KEY'],
        ],
    ]);
    $image = new Image($database);
    $controller = new ImageController($image, $userId,  $s3);
}

$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);
