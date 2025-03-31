<?php

namespace services;

use Exception;
use MyTemplate\TemplateFacade;
use repositories\FoodRepository;

class MainService
{
    private TemplateFacade $templateFacade;
    private FoodRepository $foodRepository;

    public function __construct(TemplateFacade $templateFacade, FoodRepository $foodRepository)
    {
        $this->templateFacade = $templateFacade;
        $this->foodRepository = $foodRepository;
    }

    public function handleMainPage():string
    {
        $menuItems = $this->foodRepository->getFood();
        try {
            $result = $this->templateFacade->render(__DIR__ . '\..\public\templates\main_page.html', [
                'menuItems' => $menuItems,
            ]);
        } catch (Exception $e) {
            die("Ошибка рендеринга шаблона: " . $e->getMessage());
        }
        return $result;
    }
}