<?php

use Controller\AdminController;
use Controller\CityController;
use models\FoodRepository;
use MyTemplate\TemplateFacade;
use Service\AdminService;
use services\CityService;

require_once __DIR__ . '/myTemplate/TemplateFacade.php';
require_once __DIR__ . '/controllers/CityController.php';
require_once __DIR__ . '/services/CityService.php';
require_once __DIR__ . '/models/FoodRepository.php';
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/services/AdminService.php';

const MAXIMUM_TIME_SESSION_ALIVE = 60 * 5;

$templateFacade = new TemplateFacade();
$cityService = new CityService($templateFacade);
$adminService = new AdminService($templateFacade);

$cityController = new CityController($cityService);
$adminController = new AdminController($adminService);


$requestUri = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : null;
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;

session_start();

updateSession();


$handleMapping = [
    'GET' => [
        '/process-cities' => [$cityController, 'handleCities'],
        '/admin_panel' => [$adminController, 'handleAdminPanel'],
        '/login' => [$adminController, 'handleLogin'],
        '/checkPassword' => [$adminController, 'checkPassword'],
        '/downloadFile' => [$adminController, 'downloadFile'],
        '/getFileContent' => [$adminController, 'getFileContent'],
    ],
    'POST' => [
        '/uploadFile' => [$adminController, 'uploadFile'],
    ],
    'PUT' => [
        '/deleteFile' => [$adminController, 'deleteFile'],
    ]
];

if (isset($handleMapping[$requestMethod][$requestUri])) {
    $handler = $handleMapping[$requestMethod][$requestUri];
    if (is_callable($handler)) {
        call_user_func($handler);
        exit();
    } else {
        die("Обработчик для запроса $requestMethod $requestUri не является вызываемым.");
    }
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
        'menuItems' => $menuItems,
    ]);
} catch (Exception $e) {
    die("Ошибка рендеринга шаблона: " . $e->getMessage());
}

function updateSession(): void
{
    if(!isset($_SESSION['time']))
    {
        $_SESSION['time'] = time();
    }
    else
    {
        $currentTime = time();
        if($currentTime - $_SESSION['time'] > MAXIMUM_TIME_SESSION_ALIVE){
            if(isset($_SESSION['isAuthorized'])){
                $_SESSION['isAuthorized'] = false;
            }
            $_SESSION['time'] = time();
        }
    }
}