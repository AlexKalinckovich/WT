<?php

use Controller\CityController;
use Controller\NavController;

require_once __DIR__ . '/TemplateFacade.php';
require_once __DIR__ . '/controllers/NavController.php';
require_once __DIR__ . '/controllers/CityController.php';

$templateFacade = new TemplateFacade();
$navController = new NavController();
$cityController = new CityController($templateFacade);

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

$config = json_decode(file_get_contents($configPath), true);

if ($config === null) {
    die("Ошибка декодирования файла конфигурации.");
}

$host = $config['db_host'];
$user = $config['db_user'];
$password = $config['db_password'];
$dbname = $config['db_name'];

$conn = new mysqli($host, $user, $password, $dbname);


if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

$sql = "SELECT name, description, image_path FROM food_items";
$result = $conn->query($sql);

$menuItems = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menuItems[] = [
            'name' => $row['name'],
            'description' => $row['description'],
            'image_path' => $row['image_path']
        ];
    }
}

$conn->close();

try {
    echo $templateFacade->render(__DIR__ . '\views\main_page.php', [
        'menuItems' => $menuItems
    ]);
} catch (Exception $e) {
    die("Ошибка рендеринга шаблона: " . $e->getMessage());
}