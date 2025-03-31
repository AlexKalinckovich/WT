<?php
declare(strict_types=1);
namespace repositories;
use mysqli;

class FoodRepository
{
    private $configPath;
    private $connection;
    public function __construct($configPath = __DIR__ . '/../config/config.json'){
        if (!file_exists($configPath)) {
            die("Файл конфигурации не найден.");
        }
        $this->configPath = $configPath;
        $this->sqlInit();
    }

    private function sqlInit(): void
    {

        $config = json_decode(file_get_contents($this->configPath), true);
        if ($config === null) {
            die("Ошибка декодирования файла конфигурации.");
        }

        $host = $config['db_host'];
        $user = $config['db_user'];
        $password = $config['db_password'];
        $dbname = $config['db_name'];

        $this->connection = new mysqli($host, $user, $password, $dbname);
    }

    public function getFood():array
    {
        if ($this->connection->connect_error) {
            die("Ошибка подключения к базе данных:".$this->connection->connect_error);
        }

        $sql = "SELECT name, description, image_path FROM food_items";
        $result = $this->connection->query($sql);

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
        return $menuItems;
    }

    public function closeConnection(): void
    {
        $this->connection->close();
    }
}