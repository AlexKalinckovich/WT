<?php
declare(strict_types=1);
namespace utils;
require_once __UTILS__ . '/SingletonTrait.php';

use Controller\AdminController;
use Controller\LoginController;
use Controller\MainController;
use Controller\RegistrationController;
use Exception;
use exceptions\NotCallableException;
use exceptions\PageNotFoundException;

class Router
{
    use SingletonTrait;
    private array $handleMapping;

    /**
     * @throws Exception
     */
    protected function __construct(ClassInitializer $classInitializer)
    {
        $this->handleMapping = [
            'GET' => [
                '/admin_panel'    => [$classInitializer->get(AdminController::class), 'handleAdminPanel'],
                '/checkPassword'  => [$classInitializer->get(AdminController::class), 'checkPassword'],
                '/downloadFile'   => [$classInitializer->get(AdminController::class), 'downloadFile'],
                '/getFileContent' => [$classInitializer->get(AdminController::class), 'getFileContent'],
                '/'               => [$classInitializer->get(MainController::class), 'handleMainPage'],
                '/registration'   => [$classInitializer->get(RegistrationController::class), 'handleRegistrationPage'],
                '/login'          => [$classInitializer->get(LoginController::class), 'handleLoginPage'],
            ],
            'POST' => [
                '/uploadFile'     => [$classInitializer->get(AdminController::class), 'uploadFile'],
                '/registerUser'   => [$classInitializer->get(RegistrationController::class), 'registerUser'],
                '/authorize'      => [$classInitializer->get(LoginController::class), 'handleAuthorization'],
                '/logout'         => [$classInitializer->get(LoginController::class), 'logout'],

            ],
            'PUT' => [
                '/deleteFile'     => [$classInitializer->get(AdminController::class), 'deleteFile'],
            ]
        ];
    }

    /**
     * @throws NotCallableException
     * @throws PageNotFoundException
     */
    public function route(string $requestMethod, string $requestUri): void
    {
        if (isset($this->handleMapping[$requestMethod][$requestUri])) {
            $handler = $this->handleMapping[$requestMethod][$requestUri];
            if (is_callable($handler)) {
                $response = call_user_func($handler);
                echo $response;
            } else {
                $errorMessage = "Обработчик для запроса $requestMethod $requestUri не является вызываемым.";
                Logger::error($errorMessage);
                throw new NotCallableException($errorMessage);
            }
        } else {
            $errorMessage = "Страница не найдена.";
            Logger::error($errorMessage);
            http_response_code(404);
            throw new PageNotFoundException($errorMessage);
        }
    }
}
