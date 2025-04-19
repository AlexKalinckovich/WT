<?php
declare(strict_types=1);

namespace utils;

/**
 * Интерфейс для классов, которым необходимо освобождение (закрытие) ресурсов.
 */
interface Disposable {
    /**
     * Метод освобождения ресурсов (например, закрытие соединения с базой данных).
     */
    public function dispose(): void;
}
