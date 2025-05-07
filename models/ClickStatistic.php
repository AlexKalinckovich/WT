<?php

declare(strict_types=1);

namespace models;

use utils\Data;

class ClickStatistic
{
    use Data;

    private int $id;
    private string $elementType;
    private int $elementId;
    private int $clickCount;

    /**
     * Инкрементирует счетчик кликов
     */
    public function increment(): void
    {
        $this->clickCount++;
    }

    /**
     * Возвращает составной ключ элемента
     */
    public function getCompositeKey(): string
    {
        return "{$this->elementType}:{$this->elementId}";
    }
}