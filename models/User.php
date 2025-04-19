<?php

namespace models;
require_once __UTILS__ . '/Data.php';

use utils\Data;

class User
{
    use Data;
    private int $user_id;
    private string $user_name;
    private string $user_surname;
    private string $user_email;
    private string $salt;
    private string $token;
}