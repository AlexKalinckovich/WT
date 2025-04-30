<?php

declare(strict_types=1);

namespace utils;

require_once __TEMPLATE_ENGINE__ . '/TemplateFacade.php';

require_once __CONTROLLERS__     . '/MainController.php';
require_once __CONTROLLERS__     . '/AdminController.php';
require_once __CONTROLLERS__     . '/RegistrationController.php';
require_once __CONTROLLERS__     . '/LoginController.php';

require_once __SERVICES__ . '/AdminService.php';
require_once __SERVICES__ . '/MainService.php';
require_once __SERVICES__ . '/RegistrationService.php';
require_once __SERVICES__ . '/LoginService.php';

require_once __REPOSITORIES__ . '/FoodRepository.php';
require_once __REPOSITORIES__ . '/UserRepository.php';
require_once __REPOSITORIES__ . '/OrderRepository.php';

require_once __UTILS__ . '/Router.php';
require_once __UTILS__ . '/SingletonTrait.php';
require_once __UTILS__ . '/Disposable.php';

use Controller\AdminController;
use Controller\LoginController;
use Controller\MainController;

use Controller\RegistrationController;
use Exception;

use MyTemplate\TemplateFacade;

use repositories\FoodRepository;

use repositories\OrderRepository;
use repositories\UserRepository;
use services\AdminService;
use services\LoginService;
use services\MainService;
use services\RegistrationService;


final class ClassInitializer implements Disposable{
    use SingletonTrait;

    /**
     * Массив для хранения экземпляров с ключами - именами классов.
     *
     * @var array<string, object>
     */
    private static array $instances = [];
    private static array $disposableObjects = [];

    protected function __construct() {
        self::$instances[ClassInitializer::class] = $this;
    }

    /**
     * Определения зависимостей.
     * Ключ — имя класса, значение — массив: [имя класса для создания, список зависимостей (их FQCN)]
     *
     * @var array<string, array>
     */
    private static array $definitions = [
        AdminController::class => [AdminController::class, [AdminService::class]],
        MainController::class  => [MainController::class, [MainService::class]],
        RegistrationController::class => [RegistrationController::class, [RegistrationService::class]],
        LoginController::class => [LoginController::class, [LoginService::class]],
        MainService::class     => [MainService::class, [TemplateFacade::class,
            FoodRepository::class,
            UserRepository::class,
            OrderRepository::class]],
        AdminService::class    =>  [AdminService::class, [TemplateFacade::class]],
        RegistrationService::class => [RegistrationService::class, [UserRepository::class,
            TemplateFacade::class]],
        LoginService::class => [LoginService::class, [UserRepository::class,
            TemplateFacade::class]],
        FoodRepository::class  =>  [FoodRepository::class, []],
        UserRepository::class  =>  [UserRepository::class, []],
        OrderRepository::class =>  [OrderRepository::class, []],
        TemplateFacade::class  =>  [TemplateFacade::class, []],
        Router::class          =>  [Router::class, [ClassInitializer::class]],
        ClassInitializer::class => [ClassInitializer::class,[]]
    ];

    /**
     * Вернуть объект по имени класса.
     *
     * @param string $classKey ФИ класса, указанный в качестве ключа в definitions
     * @return object
     * @throws Exception Если определение для указанного ключа не найдено
     */
    public function get(string $classKey): object {
        if (isset(self::$instances[$classKey])) {
            return self::$instances[$classKey];
        }
        if (!isset(self::$definitions[$classKey])) {
            throw new Exception("Определение для ключа '$classKey' не найдено.");
        }

        [$class, $dependenciesKeys] = self::$definitions[$classKey];

        $dependencies = [];
        foreach ($dependenciesKeys as $depKey) {
            $dependencies[] = $this->get($depKey);
        }

        if (method_exists($class, 'getInstance')) {
            $object = $class::getInstance(...$dependencies);
        } else {
            $object = new $class(...$dependencies);
        }

        self::$instances[$classKey] = $object;

        if ($object instanceof Disposable) {
            self::$disposableObjects[$classKey] = $object;
        }

        return $object;
    }

    /**
     * Очистка (сброс) кэшированных экземпляров
     *
     * @param string $classKey
     */
    public function clear(string $classKey): void {
        unset(self::$instances[$classKey]);
    }

    public function dispose(): void
    {
        foreach (self::$disposableObjects as $object) {
            $object->dispose();
        }
    }
}
