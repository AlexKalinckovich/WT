<?php

namespace utils;

use Exception;

trait SingletonTrait {
    /** @var object|null */
    protected static ?object $instance = null;

    /**
     * Возвращает единственный экземпляр класса, передавая параметры в конструктор.
     *
     * @param mixed ...$args
     * @return object
     */
    public static function getInstance(...$args): object
    {
        if (static::$instance === null) {
            static::$instance = new static(...$args);
        }
        return static::$instance;
    }

    /**
     * Запрещаем клонирование.
     */
    protected function __clone() {}

    /**
     * Запрещаем десериализацию.
     * @throws Exception
     */
    public function __wakeup() {
        throw new Exception("Десериализация синглтона недопустима.");
    }
}
