<?php

namespace services;
require_once __UTILS__ . '/SingletonTrait.php';

use Exception;
use utils\SingletonTrait;
use MyTemplate\TemplateFacade;
use repositories\FoodRepository;

class MainService
{
    use SingletonTrait;
    private TemplateFacade $templateFacade;
    private FoodRepository $foodRepository;

    protected function __construct(TemplateFacade $templateFacade, FoodRepository $foodRepository)
    {
        $this->templateFacade = $templateFacade;
        $this->foodRepository = $foodRepository;
    }

    public function handleMainPage():string
    {
        $pizzas = $this->foodRepository->getAll();
        $menuItems = array();
        foreach ($pizzas as $pizza) {
            $menuItems[] = $pizza->toArray();
        }

        try {
            $result = $this->templateFacade->render(__TEMPLATES__ .'/main_page.html', [
                'menuItems' => $menuItems,
            ]);
        } catch (Exception $e) {
            die("Ошибка рендеринга шаблона: " . $e->getMessage());
        }
        return $result;
    }
}