<?php
declare(strict_types=1);

namespace repositories;

require_once __UTILS__        . '/SingletonTrait.php';
require_once __REPOSITORIES__ . '/AbstractRepository.php';
require_once __EXCEPTIONS__   . '/NotImplementedException.php';
require_once __MODELS__       . '/Pizza.php';

use exceptions\NotImplementedException;
use exceptions\SqlConnectionErrorException;
use models\Pizza;
use utils\SingletonTrait;

class FoodRepository extends AbstractRepository {
    use SingletonTrait;

    /**
     * Возвращает все записи из таблицы food_items.
     * Реализует метод getAll интерфейса AbstractRepository.
     *
     * @return array
     * @throws SqlConnectionErrorException
     */
    public function getAll(): array {
        if ($this->connection->connect_error) {
            throw new SqlConnectionErrorException("Ошибка подключения к базе данных:" . $this->connection->connect_error);
        }

        $sql = "SELECT name, description, image_path FROM food_items";
        $result = $this->connection->query($sql);

        $menuItems = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $menuItems[] = Pizza::create(['name'        => $row["name"],
                                              'description' => $row["description"],
                                              'filePath'    => $row["image_path"]]);
            }
        }
        return $menuItems;
    }

    /**
     * Метод получения записи по идентификатору.
     * В текущей реализации не поддерживается.
     *
     * @param int $id
     * @return array
     * @throws NotImplementedException
     */
    public function getById(int $id): array
    {
        throw new NotImplementedException("Метод getById не реализован.");
    }

}
