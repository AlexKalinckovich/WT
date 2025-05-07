<?php

declare(strict_types=1);

namespace repositories;

use Exception;
use models\ClickStatistic;
use utils\SingletonTrait;

class ClickStatisticRepository extends AbstractRepository
{
    use SingletonTrait;

    /**
     * Находит запись по типу и ID элемента.
     *
     * @param string $type    Тип элемента ("painting", "movie", "book" и т.д.)
     * @param int    $elementId Идентификатор элемента
     * @return ClickStatistic|null
     * @throws Exception
     */
    public function find(string $type, int $elementId): ?ClickStatistic
    {
        $sql = "SELECT id, element_type, element_id, click_count
                FROM element_clicks
                WHERE element_type = ? AND element_id = ?
                LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        if (! $stmt) {
            throw new Exception('Не удалось подготовить запрос в ClickStatisticRepository::find');
        }
        $stmt->bind_param('si', $type, $elementId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return ClickStatistic::create([
                'id'           => (int)$row['id'],
                'elementType'  => $row['element_type'],
                'elementId'    => (int)$row['element_id'],
                'clickCount'   => (int)$row['click_count'],
            ]);
        }
        return null;
    }

    /**
     * Инкрементирует счётчик для указанного элемента.
     * Если записи нет — создаёт новую.
     *
     * @param string $type
     * @param int    $elementId
     * @return ClickStatistic  Обновлённая модель
     * @throws Exception
     */
    public function increment(string $type, int $elementId): ClickStatistic
    {
        $stat = $this->find($type, $elementId);
        if ($stat === null) {
            $data = array(
                'element_type' => $type,
                'element_id'   => $elementId,
                'click_count'  => 1,
            );
            $this->create($data);
        } else {
            $sql = "UPDATE element_clicks
                     SET click_count = click_count + 1
                     WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            if (! $stmt) {
                throw new Exception('Не удалось подготовить UPDATE в ClickStatisticRepository::increment');
            }
            $id = $stat->getId();
            $stmt->bind_param('i', $id);
            $stmt->execute();

            $stat->increment();
            return $stat;
        }
    }


    public function getById(int $id): object | null
    {
        $sql = "SELECT id, element_type, element_id, click_count
                FROM element_clicks
                WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new Exception('Не удалось подготовить запрос в ClickStatisticRepository::getById');
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return ClickStatistic::create([
                'id'           => (int)$row['id'],
                'elementType'  => $row['element_type'],
                'elementId'    => (int)$row['element_id'],
                'clickCount'   => (int)$row['click_count'],
            ]);
        }

        return null;
    }

    public function create(array $data): bool
    {
        if (
            !isset($data['element_type'], $data['element_id'], $data['click_count'])
        ) {
            throw new Exception("Недостаточно данных для создания ClickStatistic.");
        }

        $sql = "INSERT INTO element_clicks (element_type, element_id, click_count)
                VALUES (?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new Exception('Не удалось подготовить запрос в ClickStatisticRepository::create');
        }

        $stmt->bind_param(
            'sii',
            $data['element_type'],
            $data['element_id'],
            $data['click_count']
        );

        return $stmt->execute();
    }

    public function update(array $data): bool
    {
        if (
            !isset($data['element_type'], $data['element_id'], $data['click_count'])
        ) {
            throw new Exception("Недостаточно данных для обновления ClickStatistic.");
        }

        $sql = "UPDATE element_clicks
                SET click_count = ?
                WHERE element_type = ? AND element_id = ?";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new Exception('Не удалось подготовить запрос в ClickStatisticRepository::update');
        }

        $stmt->bind_param(
            'isi',
            $data['click_count'],
            $data['element_type'],
            $data['element_id']
        );

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM element_clicks WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new Exception('Не удалось подготовить запрос в ClickStatisticRepository::delete');
        }

        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    /**
     * Возвращает все статистики кликов.
     *
     * @return ClickStatistic[]
     * @throws Exception
     */
    public function getAll(): array
    {
        $sql = "SELECT id, element_type, element_id, click_count
                FROM element_clicks";
        $result = $this->connection->query($sql);
        if ($result === false) {
            throw new Exception('Ошибка получения списка кликов');
        }
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = ClickStatistic::create([
                'id'           => (int)$row['id'],
                'elementType'  => $row['element_type'],
                'elementId'    => (int)$row['element_id'],
                'clickCount'   => (int)$row['click_count'],
            ]);
        }
        $result->free();
        return $stats;
    }

    /**
     * Сбрасывает счётчик кликов для элемента (устанавливает в ноль).
     *
     * @param string $type
     * @param int    $elementId
     * @return bool
     */
    public function reset(string $type, int $elementId): bool
    {
        $sql = "UPDATE element_clicks SET click_count = 0 WHERE element_type = ? AND element_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('si', $type, $elementId);
        return $stmt->execute();
    }

}
