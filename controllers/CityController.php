<?php

namespace Controller;

use Exception;
use services\CityService;
use TemplateFacade;

class CityController
{

    private $cityService;

    public function __construct(cityService $cityService)
    {
        $this->cityService = $cityService;
    }


    public function handleCities()
    {
        echo $this->cityService->handleCities();
    }
}
