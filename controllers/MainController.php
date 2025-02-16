<?php

namespace Controller;
class MainController
{
    public function handleRequest()
    {
        $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Маршрутизация на главную страницу
        if ($urlPath === '/mainPage') {
            $this->renderHomePage();
        } else {
            $this->renderNotFound();
        }
    }

    // Отображение главной страницы
    private function renderHomePage()
    {
        require_once __DIR__ . '/../views/main_page.php';
    }

    // Отображение страницы "404 Not Found"
    private function renderNotFound()
    {
        header("HTTP/1.0 404 Not Found");
        echo "Page not found.";
    }
}

