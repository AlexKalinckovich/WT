<?php

namespace Controller;

use Exception;
use services\ClickStatisticService;
use utils\SingletonTrait;

class ClickStatisticController
{
    use SingletonTrait;

    private ClickStatisticService $clickStatisticService;

    protected function __construct(ClickStatisticService $service)
    {
        $this->clickStatisticService = $service;
    }

    public function trackClick(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)
            || empty($input['elementType'])
            || empty($input['elementId'])
            || !is_string($input['elementType'])
            || !is_numeric((int)$input['elementId'])
        ) {
            http_response_code(400);
            echo json_encode(['error'=>'Invalid payload']);
        }

        try {
            $this->clickStatisticService->increment(
                $input['elementType'],
                (int)$input['elementId']
            );
            http_response_code(204);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error'=>$e->getMessage()]);
        }
    }

    public function listStatistics(): string
    {
        echo $this->clickStatisticService->listStatistics();
    }
}