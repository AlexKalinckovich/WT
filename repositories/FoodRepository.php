<?php
declare(strict_types=1);

namespace repositories;

use models\Food;
use utils\SingletonTrait;

require_once __UTILS__        . '/SingletonTrait.php';
require_once __REPOSITORIES__ . '/AbstractRepository.php';
require_once __MODELS__       . '/Food.php';

class FoodRepository extends AbstractRepository
{
    use SingletonTrait;

    public function getAll(): array
    {
        $sql = "SELECT id, name, description, image_path FROM food_items";
        $res = $this->connection->query($sql);

        $items = [];
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $items[] = Food::create([
                    'filePath'    => $row['image_path'],
                    'name'        => $row['name'],
                    'description' => $row['description'],
                ]);
            }
        }
        return $items;
    }

    public function getById(int $id): array
    {
        $sql = "SELECT id, name, description, image_path 
                FROM food_items 
                WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();

        $items = [];
        if ($row = $res->fetch_assoc()) {
            $items[] = Food::create([
                'filePath'    => $row['image_path'],
                'name'        => $row['name'],
                'description' => $row['description'],
            ]);
        }
        return $items;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO food_items (name, description, image_path) VALUES (?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            "sss",
            $data['name'],
            $data['description'],
            $data['image_path']
        );
        return $stmt->execute();
    }

    public function update(array $data): bool
    {
        $sql = "UPDATE food_items
                   SET name = ?, description = ?, image_path = ?
                 WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            "sssi",
            $data['name'],
            $data['description'],
            $data['image_path'],
            $data['id']
        );
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM food_items WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
