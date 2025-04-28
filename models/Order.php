<?php

namespace models;

use utils\Data;

class Order
{
    use Data;

    private int  $id;
    private string $userName;
    private string $foodName;
}