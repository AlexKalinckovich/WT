<?php

declare(strict_types=1);

use models\ModelsInitializer;

require_once __DIR__ . '/models/ModelsInitializer.php';

$modelsInitializer = new ModelsInitializer();

$requestUri = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : null;
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;

try {
    $router = $modelsInitializer->get('router');
    $router->route($requestMethod, $requestUri);
} catch (Exception $e) {
    echo $e->getMessage();
    die('Router not found');
}