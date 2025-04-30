<?php

declare(strict_types=1);

use utils\ClassInitializer;
use utils\Router;
use utils\Logger;

require_once __DIR__   . '/config/pathConfig.php';
require_once __UTILS__ . '/ClassInitializer.php';
require_once __UTILS__ . '/Logger.php';

$classInitializer = ClassInitializer::getInstance();

$requestUri = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : null;
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
try {
    $router = $classInitializer->get(Router::class);
    $router->route($requestMethod, $requestUri);
} catch (Exception $e) {
    Logger::error($e->getMessage(),[$e]);
}

$classInitializer->dispose();




