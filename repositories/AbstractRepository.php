<?php
declare(strict_types=1);

namespace repositories;
require_once __UTILS__ . '/Disposable.php';



use exceptions\ConfigurationFileNotFoundException;
use exceptions\NotImplementedException;
use mysqli;
use utils\Disposable;
use utils\Logger;

/**
 * Интерфейс репозитория, аналогичный JPA-репозиторию.
 * Содержит типичные методы выборки данных.
 * Все репозитории также должны реализовывать интерфейс Disposable.
 */
abstract class AbstractRepository implements Disposable
{
    protected mysqli $connection;

    /**
     * @throws ConfigurationFileNotFoundException
     */
    protected function __construct(string $configPath = __CONFIG__ . '/config.json') {
        if (!file_exists($configPath)) {
            $errorMessage = 'Файл конфигурации не найден.';
            Logger::error($errorMessage);
            throw new ConfigurationFileNotFoundException($errorMessage);
        }
        $this->sqlInit(__CONFIG__ . '/config.json');
    }


    protected function sqlInit(string $configPath): void {
        $config = json_decode(file_get_contents($configPath), true);
        if ($config === null) {
            die("Ошибка декодирования файла конфигурации.");
        }

        $host = $config['db_host'];
        $user = $config['db_user'];
        $password = $config['db_password'];
        $dbname = $config['db_name'];

        $this->connection = new mysqli($host, $user, $password, $dbname);
    }

    /**
     * Возвращает все записи.
     *
     * @return array
     */
    public abstract function getAll(): array;

    /**
     * Возвращает запись по идентификатору.
     *
     * @param int $id
     * @return object|null
     */
    public abstract function getById(int $id): object | null;

    public abstract function create(array $data): bool;
    public abstract function delete(int $id): bool;
    public abstract function update(array $data): bool;

    public function dispose(): void
    {
        $this->connection->close();
    }
}
