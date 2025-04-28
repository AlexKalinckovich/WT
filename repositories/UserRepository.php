<?php
declare(strict_types=1);

namespace repositories;

use models\User;
use mysqli_result;
use utils\SingletonTrait;

require_once __UTILS__        . '/SingletonTrait.php';
require_once __REPOSITORIES__ . '/AbstractRepository.php';
require_once __MODELS__       . '/User.php';

class UserRepository extends AbstractRepository
{
    use SingletonTrait;

    public function getAll(): array
    {
        $sql = "SELECT user_id, user_name, user_surname, user_email, salt, token FROM users";
        return $this->fetchUsers($sql);
    }

    public function getById(int $id): array
    {
        $sql = "SELECT user_id, user_name, user_surname, user_email, salt, token 
                FROM users 
                WHERE user_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $this->mapUsers($res);
    }

    private function fetchUsers(string $sql): array
    {
        $res = $this->connection->query($sql);
        return $this->mapUsers($res);
    }

    private function mapUsers(mysqli_result $res): array
    {
        $users = [];
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $users[] = User::create([
                    'userId'      => (int)$row['user_id'],
                    'userName'    => $row['user_name'],
                    'userSurname' => $row['user_surname'],
                    'userEmail'   => $row['user_email'],
                    'salt'        => $row['salt'],
                    'token'       => $row['token'],
                ]);
            }
        }
        return $users;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO users (user_name, user_surname, user_email, salt, token)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            "sssss",
            $data['user_name'],
            $data['user_surname'],
            $data['user_email'],
            $data['salt'],
            $data['token']
        );
        return $stmt->execute();
    }

    public function update(array $data): bool
    {
        $sql = "UPDATE users
                   SET user_name = ?, user_surname = ?, user_email = ?, salt = ?, token = ?
                 WHERE user_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            "sssssi",
            $data['user_name'],
            $data['user_surname'],
            $data['user_email'],
            $data['salt'],
            $data['token'],
            $data['user_id']
        );
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
