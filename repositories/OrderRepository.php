<?php
declare(strict_types=1);

namespace repositories;

use models\Order;
use mysqli_result;
use utils\SingletonTrait;

require_once __UTILS__        . '/SingletonTrait.php';
require_once __REPOSITORIES__ . '/AbstractRepository.php';
require_once __MODELS__       . '/Order.php';

class OrderRepository extends AbstractRepository
{
    use SingletonTrait;

    public function getAll(): array
    {
        $sql = <<<SQL
                    SELECT 
                      ufo.id,
                      u.user_name   AS userName,
                      f.name        AS foodName
                    FROM user_food_orders AS ufo
                    JOIN users AS u  ON ufo.user_id = u.user_id
                    JOIN food_items AS f ON ufo.food_id = f.id
                  SQL;
        return $this->mapOrders($this->connection->query($sql));
    }

    public function getById(int $id): array
    {
        $sql = <<<SQL
                    SELECT 
                      ufo.id,
                      u.user_name   AS userName,
                      f.name        AS foodName
                    FROM user_food_orders AS ufo
                    JOIN users AS u  ON ufo.user_id = u.user_id
                    JOIN food_items AS f ON ufo.food_id = f.id
                    WHERE ufo.id = ?
                  SQL;
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $this->mapOrders($stmt->get_result());
    }

    private function mapOrders(mysqli_result $res): array
    {
        $orders = [];
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $orders[] = Order::create([
                    'id'       => (int)$row['id'],
                    'userName' => $row['userName'],
                    'foodName' => $row['foodName'],
                ]);
            }
        }
        return $orders;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO user_food_orders (user_id, food_id) VALUES (?, ?)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            "ii",
            $data['user_id'],
            $data['food_id']
        );
        return $stmt->execute();
    }

    public function update(array $data): bool
    {
        $sql = "UPDATE user_food_orders 
                   SET user_id = ?, food_id = ?
                 WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            "iii",
            $data['user_id'],
            $data['food_id'],
            $data['id']
        );
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM user_food_orders WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
