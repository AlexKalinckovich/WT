<?php
// Чтение конфигурации из JSON файла
$configPath = __DIR__ . '/config.json';

if (!file_exists($configPath)) {
    die("Файл конфигурации не найден.");
}

// Декодирование JSON файла в массив PHP
$config = json_decode(file_get_contents($configPath), true);

if ($config === null) {
    die("Ошибка декодирования файла конфигурации.");
}

// Подключение к базе данных MySQL
$host = $config['db_host'];
$user = $config['db_user'];
$password = $config['db_password'];
$dbname = $config['db_name'];

$conn = new mysqli($host, $user, $password, $dbname);

// Проверка соединения с базой данных
if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

// Запрос данных о меню
$sql = "SELECT name, description, image_path FROM food_items";
$result = $conn->query($sql);

// Инициализация массива для хранения данных о блюдах
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

// Передача данных на страницу отображения
include __DIR__ . '/main_page.php';