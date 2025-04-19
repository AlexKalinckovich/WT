<?php

namespace utils;

use BadMethodCallException;

trait Data {

    private function __construct(){}

    /**
     * Магический метод для динамических вызовов getXXX и setXXX.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $arguments) {
        $prefix = substr($method, 0, 3);
        $property = lcfirst(substr($method, 3));

        if ($prefix === 'get') {
            if (property_exists($this, $property)) {
                return $this->$property;
            }
        } elseif ($prefix === 'set') {
            if (property_exists($this, $property)) {
                $this->$property = $arguments[0];
                return $this;
            }
        }
        throw new BadMethodCallException("Метод $method не существует в классе " . get_class($this));
    }

    /**
     * Магический метод для доступа к недоступным (private/protected) свойствам.
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        trigger_error("Свойство $name не найдено в классе " . get_class($this));
        return null;
    }

    /**
     * Магический метод для установки значения недоступным (private/protected) свойств.
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, mixed $value) {
        if (property_exists($this, $name)) {
            $this->$name = $value;
            return;
        }
        trigger_error("Свойство $name не найдено в классе " . get_class($this), E_USER_NOTICE);
    }

    /**
     * Формирует строковое представление объекта.
     *
     * @return string
     */
    public function __toString(): string {
        $props = get_object_vars($this);
        return sprintf("%s %s", get_class($this), json_encode($props, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Сравнивает два объекта по значениям их свойств.
     *
     * @param mixed $other
     * @return bool
     */
    public function equals(mixed $other): bool {
        if (get_class($this) !== get_class($other)) {
            return false;
        }

        $thisVars = get_object_vars($this);
        $otherVars = get_object_vars($other);

        return $this->deepCompare($thisVars, $otherVars);
    }

    private function deepCompare(mixed $a, mixed $b): bool {
        if(is_array($a) && is_array($b)) {
            return $this->arrayCompare($a, $b);
        }

        if(is_object($a) && is_object($b)) {
            return $this->objectCompare($a, $b);
        }

        return $a === $b;
    }

    private function objectCompare(object $a, object $b): bool {
        if(get_class($a) !== get_class($b)) {
            return false;
        }

        if(method_exists($a, 'equals')) {
            return $a->equals($b);
        }else if(method_exists($b, 'equals')) {
            return $b->equals($a);
        }

        return $this->deepCompare(get_object_vars($a), get_object_vars($b));
    }

    private function arrayCompare(array $a, array $b): bool {
        if(count($a) !== count($b)) {
            return false;
        }
        foreach($a as $k => $aVal) {
            if(!array_key_exists($k, $b)) {
                return false;
            }
            $bVal = $b[$k];

            if(!$this->deepCompare($aVal, $bVal)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Вычисляет хэш-код объекта на основе его свойств.
     *
     * @return int
     */
    public function hashCode(): int {
        return crc32(json_encode(get_object_vars($this)));
    }

    /**
     * Статический конструктор для удобного создания объекта из массива данных.
     *
     * @param array $data Ассоциативный массив, где ключи соответствуют именам свойств.
     * @return static
     */
    public static function create(array $data = []): static
    {
        $instance = new static();
        foreach ($data as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($instance, $setter)) {
                $instance->$setter($value);
            } elseif (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }
        return $instance;
    }
}
