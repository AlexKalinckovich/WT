<?php

use Controller\CityController;
use Controller\NavController;
use models\FoodRepository;
use services\CityService;

require_once __DIR__ . '/TemplateFacade.php';
require_once __DIR__ . '/controllers/NavController.php';
require_once __DIR__ . '/controllers/CityController.php';
require_once __DIR__ . '/services/CityService.php';
require_once __DIR__ . '/models/FoodRepository.php';

$templateFacade = new TemplateFacade();
$cityService = new CityService($templateFacade);

$navController = new NavController();
$cityController = new CityController($cityService);

$requestUri = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : null;
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;

if ($requestMethod === 'GET' && $requestUri === '/process-cities') {
    $cityController->handleCities();
    exit();
}

if ($requestMethod === 'GET' && $requestUri === '/nav') {
    $navController->updateActive();
    exit();
}

$configPath = __DIR__ . '/config/config.json';

if (!file_exists($configPath)) {
    die("Файл конфигурации не найден.");
}
$foodRepository = new FoodRepository($configPath);


$menuItems = $foodRepository->getFood();
$foodRepository->closeConnection();

try {
    echo $templateFacade->render(__DIR__ . '\views\main_page.php', [
        'menuItems' => $menuItems
    ]);
} catch (Exception $e) {
    die("Ошибка рендеринга шаблона: " . $e->getMessage());
}