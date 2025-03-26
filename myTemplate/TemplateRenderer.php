<?php

namespace MyTemplate;

class TemplateRenderer
{
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