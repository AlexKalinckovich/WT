<?php

namespace services;

use Exception;
use MyTemplate\TemplateFacade;

class CityService
{

    /**
     * @var TemplateFacade
     */
    private $templateFacade;

    public function __construct(TemplateFacade $templateFacade)
    {
        $this->templateFacade = $templateFacade;
    }

    public function handleCities()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header("HTTP/1.1 405 Method Not Allowed");
            exit();
        }

        if (!isset($_GET["cities"])) {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(['error' => 'Не указаны города.']);
            exit();
        }

        if(empty(trim($_GET["cities"]))){
            header("Location: /");
            exit();
        }

        $citiesInput = $_GET["cities"];
        $citiesArray = explode(',', $citiesInput);
        $processedCities = [];
        foreach ($citiesArray as $city) {
            $city = trim($city);
            if (strlen($city) > 0) {
                $formattedCity = ucfirst(strtolower($city));
                $processedCities[] = $formattedCity;
            }
        }

        $processedCities = array_unique($processedCities);

        sort($processedCities, SORT_STRING);

        header('Content-Type: application/json');
        $result = '';
        try {
            header("Content-Type: text/html; charset=UTF-8");
            $result =  $this->templateFacade->render(
                __DIR__ . '/../templates/cities_template.html',
                ['cities' => $processedCities]
            );
        } catch (Exception $e) {
            echo "Ошибка: " . $e->getMessage();
        }
        return $result;
    }
}