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
        $sql = "SELECT user_id, user_name, user_surname, user_email, server_salt, token FROM users";
        return $this->fetchUsers($sql);
    }

    public function getById(int $id): array
    {
        $sql = "SELECT user_id, user_name, user_surname, user_email, server_salt, token 
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
                    'salt'        => $row['server_salt'],
                    'token'       => $row['token'],
                ]);
            }
        }
        return $users;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO users (user_name, user_surname, user_email,password_hash, server_salt, token)
            VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            "ssssss",
            $data['user_name'],
            $data['user_surname'],
            $data['user_email'],
            $data['password_hash'],
            $data['server_salt'],
            $data['token']
        );

        return $stmt->execute();
    }


    public function update(array $data): bool
    {
        $sql = "UPDATE users
                   SET user_name = ?, user_surname = ?, user_email = ?, server_salt = ?, token = ?
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

    public function getByEmail(string $email): array
    {
        $sql  = "SELECT user_id, user_name, user_surname, user_email, password_hash, server_salt, token
               FROM users
              WHERE user_email = ? LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $email);
        return $this->getMatchUsers($stmt);
    }

    public function updateRememberToken(int $userId, string $token): bool
    {
        $sql  = "UPDATE users SET token = ? WHERE user_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("si", $token, $userId);
        return $stmt->execute();
    }

    public function getByToken(string $token): array
    {
        $sql  = "SELECT user_id, user_name, user_surname, user_email, password_hash, server_salt, token
               FROM users
              WHERE token = ? LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $token);
        return $this->getMatchUsers($stmt);
    }

    /**
     * @param bool|\mysqli_stmt $stmt
     * @return array
     */
    private function getMatchUsers(bool|\mysqli_stmt $stmt): array
    {
        $stmt->execute();
        $res = $stmt->get_result();

        $users = [];
        while ($row = $res->fetch_assoc()) {
            $users[] = User::create([
                'userId' => (int)$row['user_id'],
                'userName' => $row['user_name'],
                'userSurname' => $row['user_surname'],
                'userEmail' => $row['user_email'],
                'passwordHash' => $row['password_hash'],
                'salt' => $row['server_salt'],
                'token' => $row['token'],
            ]);
        }
        return $users;
    }

}
