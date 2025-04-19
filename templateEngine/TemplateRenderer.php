<?php

namespace MyTemplate;
use utils\SingletonTrait;

require_once __UTILS__ . '/SingletonTrait.php';


class TemplateRenderer
{
    use SingletonTrait;
    private array $data = [];
    public function render(string $cacheFile, array $data): string
    {
        $this->data = $data;
        extract($this->data);
        ob_start();
        include $cacheFile;
        return ob_get_clean();
    }
}