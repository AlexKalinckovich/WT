<?php

declare(strict_types=1);

use services\AuthorizationService;
use utils\ClassLoader;
use utils\Logger;
use utils\Router;

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__   . '/config/pathConfig.php';
require_once __UTILS__ . '/ClassLoader.php';

const __MAIN_PAGE__ = '/';
const __ADMIN_PAGE__ = '/adminPanel';

try {
    $classInitializer = ClassLoader::getInstance();
}catch (Exception $e){
    Logger::error("[ClassLoader] FATAL ERROR: " . $e->getMessage());
    exit;
}

$requestUri = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : null;
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;

$segments = array_values(array_filter(explode('/', $requestUri)));
$lang = 'en';
$last = end($segments);
if (in_array($last, ['ru', 'en'], true)) {
    $lang = $last;
    array_pop($segments);
    $requestUri = '/' . implode('/', $segments);
}


define('__CURRENT_LANG__', $lang);

if($requestUri === __MAIN_PAGE__ || $requestMethod === __ADMIN_PAGE__) {
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




