<?php
declare(strict_types=1);

namespace utils;

use Exception;
use exceptions\NotCallableException;
use exceptions\PageNotFoundException;

class Router
{
    use SingletonTrait;

    private array $handleMapping = [];
    private static string $routesPath = __CONFIG__ . '/routes.yaml';

    /**
     * @throws Exception
     */
    protected function __construct(ClassLoader $ClassLoader)
    {
        $this->loadRoutes($ClassLoader);
    }

    /**
     * @throws Exception
     */
    private function loadRoutes(ClassLoader $ClassLoader): void
    {
        if (!file_exists(self::$routesPath)) {
            throw new Exception("Routes config file not found");
        }

        $config = yaml_parse_file(self::$routesPath);
        if (!isset($config['routes'])) {
            throw new Exception("Invalid routes config structure");
        }

        foreach ($config['routes'] as $method => $routes) {
            foreach ($routes as $route => $handlerConfig) {
                $controller = $ClassLoader->get($handlerConfig['controller']);
                $this->handleMapping[$method][$route] = [
                    $controller,
                    $handlerConfig['method']
                ];
            }
        }
    }

    /**
     * @throws NotCallableException
     * @throws PageNotFoundException
     */
    public function route(string $requestMethod, string $requestUri): void
    {
        $requestUri = $this->normalizeUri($requestUri);

        if (isset($this->handleMapping[$requestMethod][$requestUri])) {
            $handler = $this->handleMapping[$requestMethod][$requestUri];
            if (is_callable($handler)) {
                $response = call_user_func($handler);
                echo $response;
            } else {
                $error = "Handler for $requestMethod $requestUri is not callable";
                Logger::error($error);
                throw new NotCallableException($error);
            }
        } else {
            $error = "Page not found: $requestMethod $requestUri";
            Logger::error($error);
            http_response_code(404);
            throw new PageNotFoundException($error);
        }
    }

    private function normalizeUri(string $uri): string
    {
        if($uri !== '/'){
            $uri = parse_url(rtrim($uri, '/'), PHP_URL_PATH) ?? '';
        }
        return $uri;
    }
}