<?php

namespace Controller;

use services\CityService;

class CityController
{

    private CityService $cityService;

    public function __construct(cityService $cityService)
    {
        $this->cityService = $cityService;
    }

    public function handleCities(): string
    {
        return $this->cityService->handleCities();
    }
}
