<?php
declare(strict_types=1);
namespace models;

use Controller\AdminController;
use Controller\CityController;
use Controller\MainController;
use Exception;
use MyTemplate\TemplateFacade;
use repositories\FoodRepository;
use Service\AdminService;
use Services\CityService;
use services\MainService;

require_once __DIR__ . '/../templateEngine/TemplateFacade.php';

require_once __DIR__ . '/../controllers/CityController.php';
require_once __DIR__ . '/../controllers/MainController.php';
require_once __DIR__ . '/../controllers/AdminController.php';

require_once __DIR__ . '/../services/CityService.php';
require_once __DIR__ . '/../services/AdminService.php';
require_once __DIR__ . '/../services/MainService.php';


require_once __DIR__ . '/../repositories/FoodRepository.php';
require_once __DIR__ . '/../models/Router.php';
class ModelsInitializer {
    private static array $instances = [];

    public function __construct()
    {
        self::$instances['modelsInitializer'] = $this;
    }

    private array $definitions = [
        'adminController' => [AdminController::class, ['adminService']],
        'cityController'  => [CityController::class, ['cityService']],
        'mainController'  => [MainController::class, ['mainService']],
        'mainService'     => [MainService::class, ['templateFacade', 'foodRepository']],
        'adminService'    => [AdminService::class, ['templateFacade']],
        'cityService'     => [CityService::class, ['templateFacade']],
        'foodRepository'  => [FoodRepository::class, []],
        'templateFacade'  => [TemplateFacade::class, []],
        'router'          => [Router::class, ['modelsInitializer']],
    ];

    /**
     * Получение объекта по ключу.
     *
     * @param string $key
     * @return object
     * @throws Exception
     */
    public function get(string $key): object
    {
        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }
        if (!isset($this->definitions[$key])) {
            throw new Exception("Определение для ключа '{$key}' не найдено.");
        }

        [$class, $dependenciesKeys] = $this->definitions[$key];

        $dependencies = [];
        foreach ($dependenciesKeys as $depKey) {
            $dependencies[] = $this->get($depKey);
        }

        $object = new $class(...$dependencies);

        self::$instances[$key] = $object;
        return $object;
    }
}
