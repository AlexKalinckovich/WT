<?php

namespace MyTemplate;

class TemplateCache
{
    private string $cacheDir;

    public function __construct(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
    }

    public function getCacheFile(string $templatePath): string
    {
        return $this->cacheDir . '/' . md5(realpath($templatePath)) . '.php';
    }

    public function isFresh(string $cacheFile, string $templatePath): bool
    {
        return file_exists($cacheFile) && filemtime($cacheFile) >= filemtime($templatePath);
    }

    public function write(string $cacheFile, string $content): void
    {
        file_put_contents($cacheFile, $content);
    }
}