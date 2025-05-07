<?php
declare(strict_types=1);

namespace repositories;

use models\User;
use mysqli_result;
use mysqli_stmt;
use utils\SingletonTrait;

require_once __UTILS__        . '/SingletonTrait.php';
require_once __REPOSITORIES__ . '/AbstractRepository.php';
require_once __MODELS__       . '/User.php';

class UserRepository extends AbstractRepository
{
    use SingletonTrait;

    /**
     * Возвращает всех пользователей.
     * @return User[]
     */
    public function getAll(): array
    {
        $sql = "SELECT user_id, user_name, user_surname, user_email, password_hash, server_salt, token, is_authorized FROM users";
        return $this->mapUsers($this->connection->query($sql));
    }

    /**
     * Возвращает пользователя по ID или null.
     */
    public function getById(int $id): ?User
    {
        $sql = "SELECT user_id, user_name, user_surname, user_email, password_hash, server_salt, token, is_authorized
                FROM users
                WHERE user_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $id);
        return $this->getSingleUser($stmt);
    }

    /**
     * Создает нового пользователя и возвращает ID.
     * @param array    $data   Данные для вставки
     * @param int|null $newId  [out] Запишет ID созданного пользователя
     * @return bool
     */
    public function create(array $data, ?int &$newId = null): bool
    {
        $sql = "INSERT INTO users
              (user_name, user_surname, user_email, password_hash, server_salt, token, is_authorized)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            "ssssssi",
            $data['user_name'],
            $data['user_surname'],
            $data['user_email'],
            $data['password_hash'],
            $data['server_salt'],
            $data['token'],
            $data['isAuthorized']
        );
        $ok = $stmt->execute();
        if ($ok) {
            $newId = $this->connection->insert_id;
        }
        return $ok;
    }

    /**
     * Обновляет данные пользователя, включая флаг авторизации.
     * @param array $data
     * @return bool
     */
    public function update(array $data): bool
    {
        $sql = "UPDATE users
                SET   user_name     = ?, 
                      user_surname  = ?, 
                      user_email    = ?, 
                      password_hash = ?, 
                      server_salt   = ?, 
                      token         = ?, 
                      is_authorized = ?
                WHERE user_id       = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(
            "sssssiii",
            $data['user_name'],
            $data['user_surname'],
            $data['user_email'],
            $data['password_hash'],
            $data['server_salt'],
            $data['token'],
            $data['isAuthorized'],
            $data['user_id']
        );
        return $stmt->execute();
    }

    /**
     * Удаляет пользователя по ID.
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Возвращает пользователя по email или null.
     */
    public function getByEmail(string $email): ?User
    {
        $sql = "SELECT user_id, 
                       user_name, 
                       user_surname, 
                       user_email, 
                       password_hash, 
                       server_salt, 
                       token, 
                       is_authorized
                FROM users
                WHERE user_email = ? LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $email);
        return $this->getSingleUser($stmt);
    }

    /**
     * Обновляет запомнить токен.
     */
    public function updateRememberToken(int $userId, string $token): bool
    {
        $sql = "UPDATE users SET token = ? WHERE user_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("si", $token, $userId);
        return $stmt->execute();
    }

    /**
     * Возвращает пользователя по токену или null.
     */
    public function getByToken(string $token): ?User
    {
        $sql = "SELECT user_id,
                       user_name, 
                       user_surname, 
                       user_email, 
                       password_hash, 
                       server_salt, 
                       token, 
                       is_authorized
                FROM users
                WHERE token = ? LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $token);
        return $this->getSingleUser($stmt);
    }

    /**
     * Выполняет SELECT через mysqli_stmt и возвращает одну модель или null.
     */
    private function getSingleUser(mysqli_stmt $stmt): ?User
    {
        $stmt->execute();
        $res = $stmt->get_result();
        $users = $this->mapUsers($res);
        $res->free();
        return $users[0] ?? null;
    }

    /**
     * Картирует результаты mysqli_result в массив User.
     * @param mysqli_result $res
     * @return User[]
     */
    private function mapUsers(mysqli_result $res): array
    {
        $users = [];
        while ($row = $res->fetch_assoc()) {
            $users[] = User::create([
                'userId'       => (int)$row['user_id'],
                'userName'     => $row['user_name'],
                'userSurname'  => $row['user_surname'],
                'userEmail'    => $row['user_email'],
                'passwordHash' => $row['password_hash'],
                'salt'         => $row['server_salt'],
                'token'        => $row['token'],
                'isAuthorized' => (bool)$row['is_authorized'],
            ]);
        }
        return $users;
    }

}
