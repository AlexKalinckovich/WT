<?php

namespace services;

use Exception;
use models\ClickStatistic;
use repositories\ClickStatisticRepository;
use utils\SingletonTrait;

class ClickStatisticService
{
    use SingletonTrait;

    private ClickStatisticRepository $clickStatisticRepository;

    protected function __construct(ClickStatisticRepository $repo)
    {
        $this->clickStatisticRepository = $repo;
    }

    /**
     * Инкрементирует счетчик кликов для указанного элемента.
     *
     * @param string $type
     * @param int    $elementId ID элемента
     * @return ClickStatistic  Обновлённая модель
     * @throws Exception
     */
    public function increment(string $type, int $elementId): ClickStatistic
    {
        return $this->clickStatisticRepository->increment($type, $elementId);
    }

    /**
     * Возвращает JSON-список всей статистики кликов:
     * [ { elementType, elementId, clickCount }, ... ]
     */
    public function listStatistics(): string
    {
        try {
            $stats = $this->clickStatisticRepository->getAll();
            $out = array_map(fn($stat) => [
                'elementType' => $stat->getElementType(),
                'elementId'   => $stat->getElementId(),
                'clickCount'  => $stat->getClickCount(),
            ], $stats);
            header('Content-Type: application/json');
            $result = json_encode($out);
        } catch (Exception $e) {
            http_response_code(500);
            $result =  json_encode(['error'=>$e->getMessage()]);
        }
        return $result;
    }

    /**
     * Возвращает список всех записей статистики.
     *
     * @return ClickStatistic[]
     * @throws Exception
     */
    public function getAll(): array
    {
        return $this->clickStatisticRepository->getAll();
    }

    /**
     * Сбрасывает счётчик для конкретного элемента.
     *
     * @param string $type
     * @param int    $elementId
     * @return bool
     */
    public function reset(string $type, int $elementId): bool
    {
        return $this->clickStatisticRepository->reset($type, $elementId);
    }
}