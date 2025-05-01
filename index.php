<?php

declare(strict_types=1);

use services\AuthorizationService;
use utils\ClassLoader;
use utils\Logger;
use utils\Router;

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__   . '/config/pathConfig.php';
require_once __UTILS__ . '/Logger.php';
require_once __UTILS__ . '/ClassLoader.php';

const __LOGIN_URI__ = '/login';
const __REGISTRATION_URI__ = '/registration';

try {
    $classInitializer = ClassLoader::getInstance();
}catch (Exception $e){
    Logger::error("[ClassLoader] FATAL ERROR: " . $e->getMessage());
    exit;
}
session_start();

$requestUri = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : null;
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;

if($requestUri !== __LOGIN_URI__ && $requestMethod !== __REGISTRATION_URI__) {
    $authorizationService = $classInitializer->get(AuthorizationService::class);
    $authorizationService->authorize();
}

try {
    $router = $classInitializer->get(Router::class);
    $router->route($requestMethod, $requestUri);
} catch (Exception $e) {
    Logger::error($e->getMessage(),[$e]);
}

$classInitializer->dispose();




