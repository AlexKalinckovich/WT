<?php
declare(strict_types=1);
namespace models;

use Exception;
use JetBrains\PhpStorm\NoReturn;

class Router
{
    private array $handleMapping;

    /**
     * @throws Exception
     */
    public function __construct(ModelsInitializer $modelsInitializer)
    {
        $this->handleMapping = [
            'GET' => [
                '/process-cities' => [$modelsInitializer->get('cityController'), 'handleCities'],
                '/admin_panel'    => [$modelsInitializer->get('adminController'), 'handleAdminPanel'],
                '/login'          => [$modelsInitializer->get('adminController'), 'handleLogin'],
                '/checkPassword'  => [$modelsInitializer->get('adminController'), 'checkPassword'],
                '/downloadFile'   => [$modelsInitializer->get('adminController'), 'downloadFile'],
                '/getFileContent' => [$modelsInitializer->get('adminController'), 'getFileContent'],
                '/'               => [$modelsInitializer->get('mainController'), 'handleMainPage'],
            ],
            'POST' => [
                '/uploadFile'     => [$modelsInitializer->get('adminController'), 'uploadFile'],
            ],
            'PUT' => [
                '/deleteFile'     => [$modelsInitializer->get('adminController'), 'deleteFile'],
            ]
        ];
    }

    #[NoReturn]
    public function route(string $requestMethod, string $requestUri): void
    {
        if (isset($this->handleMapping[$requestMethod][$requestUri])) {
            $handler = $this->handleMapping[$requestMethod][$requestUri];
            if (is_callable($handler)) {
                $response = call_user_func($handler);
                echo $response;
                exit;
            } else {
                die("Обработчик для запроса $requestMethod $requestUri не является вызываемым.");
            }
        } else {
            http_response_code(404);
            die("Страница не найдена.");
        }
    }
}
