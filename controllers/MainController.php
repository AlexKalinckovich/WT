<?php

namespace Controller;

use services\MainService;

class MainController
{
    private MainService $mainService;

    public function __construct(MainService $mainService)
    {
        $this->mainService = $mainService;
    }

    public function handleMainPage(): string
    {
        return $this->mainService->handleMainPage();
    }
}