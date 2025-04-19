<?php

namespace Controller;
require_once __UTILS__ . '/SingletonTrait.php';


use utils\SingletonTrait;
use services\MainService;

class MainController
{
    use SingletonTrait;
    private MainService $mainService;

    protected function __construct(MainService $mainService)
    {
        $this->mainService = $mainService;
    }

    public function handleMainPage(): string
    {
        return $this->mainService->handleMainPage();
    }
}