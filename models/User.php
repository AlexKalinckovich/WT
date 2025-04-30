<?php

namespace models;
require_once __UTILS__ . '/Data.php';

use utils\Data;

class User
{
    use Data;
    private int $userId;
    private string $userName;
    private string $userSurname;
    private string $userEmail;
    private string $passwordHash;
    private string $salt;
    private string $token;
}