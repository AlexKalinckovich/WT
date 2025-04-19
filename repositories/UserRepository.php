<?php

namespace repositories;

use exceptions\SqlConnectionErrorException;
use models\User;
use utils\SingletonTrait;

require_once __UTILS__ . '/SingletonTrait.php';

class UserRepository extends AbstractRepository
{
    use SingletonTrait;

    /**
     * @return array
     * @throws SqlConnectionErrorException
     */
    public function getAll(): array
    {
        if($this->connection->connect_error){
            throw new SqlConnectionErrorException("Ошибка подключения к базе данных:" . $this->connection->connect_error);
        }

        $sql = "SELECT user_id, user_name, user_email, salt, token FROM USERS";
        return $this->getQueryResult($sql);
    }

    /**
     * @throws SqlConnectionErrorException
     */
    public function getById(int $id): array
    {
        if($this->connection->connect_error){
            throw new SqlConnectionErrorException("Ошибка подключения к базе данных:" . $this->connection->connect_error);
        }

        $sql = "SELECT user_id, user_name, user_email, salt, token FROM USERS WHERE user_id = $id";
        return $this->getQueryResult($sql);
    }

    private function getQueryResult(string $sql):array
    {
        $result = $this->connection->query($sql);
        $users = [];
        while($result->num_rows > 0){
            while ($row = $result->fetch_assoc()){
                $users[] = User::create(['user_id'    => $row['user_id'],
                    'user_name'  => $row['user_name'],
                    'user_email' => $row['user_email'],
                    'salt'       => $row['salt'],
                    'token'      => $row['token']]);
            }
        }
        return $users;
    }
}