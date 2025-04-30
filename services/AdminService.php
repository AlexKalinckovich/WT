<?php

namespace services;

require_once __UTILS__ . '/SingletonTrait.php';

use Exception;
use MyTemplate\TemplateFacade;
use utils\Logger;
use utils\SingletonTrait;

defined('__ADMIN_FILE_NAME__') or define('__ADMIN_FILE_NAME__', 'admin');

class AdminService
{
    use SingletonTrait;
    private TemplateFacade $templateFacade;

    protected function __construct(TemplateFacade $templateFacade)
    {
        $this->templateFacade = $templateFacade;
    }

    /**
     * @throws Exception
     */

    public function validateCredentials(string $username, string $password): bool
    {
        $htpasswdPath = __CONFIG__ . '/.htpasswd';
        if (!file_exists($htpasswdPath)) {
            throw new Exception('Файл .htpasswd не найден');
        }

        $lines = file($htpasswdPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            list($storedUsername, $storedHash) = explode(':', $line);
            $hash = password_hash($storedHash, PASSWORD_DEFAULT);
            if ($storedUsername === $username && password_verify($password, $hash)) {
                return true;
            }
        }

        return false;
    }

    public function getAllowedDirs(): array
    {
        return [
            realpath(__PUBLIC__),
            realpath(__CSS__),
            realpath(__JS__),
            realpath(__IMAGES__),
            realpath(__TEMPLATES__)
        ];
    }

    /**
     * @throws Exception
     */
    public function getAllowedFiles(?string $currentDir = null): array {

        if ($currentDir === null) {
            return $this->getBaseDirs();
        }

        $currentDir = $this->handleCurrentAndParentPaths($currentDir);

        $isAllowed = $this->isCurrentDirAllowedForAdmin($currentDir);

        if (!$isAllowed) {
            throw new Exception("Access denied");
        }

        return $this->getCurrentDirFiles($currentDir);
    }

    private function getBaseDirs(): array
    {
        $allowedDirs = $this->getAllowedDirs();
        return array_map(function($dir) {
            return [
                'name' => basename($dir),
                'is_dir' => true,
                'path' => $dir,
                'fullPath' => realpath(__PUBLIC__ . DIRECTORY_SEPARATOR . $dir)
            ];
        }, $allowedDirs);
    }

    private function getCurrentDirFiles(string $currentDir): array
    {
        $fullPath = __ROOT__ . DIRECTORY_SEPARATOR .  $currentDir;
        $items = scandir($fullPath);
        $files = [];
        foreach ($items as $item) {

            if(!str_starts_with($item, __ADMIN_FILE_NAME__))
            {

                $itemPath = $currentDir   . DIRECTORY_SEPARATOR . $item;
                $itemFullPath = $fullPath . DIRECTORY_SEPARATOR . $item;

                $files[] = [
                    'name' => $item,
                    'is_dir' => is_dir($itemFullPath),
                    'path' => $itemPath,
                    'fullPath' => realpath($itemFullPath)
                ];
            }
        }

        return $files;
    }

    private function handleCurrentAndParentPaths(string $currentDir): string
    {
        if (str_ends_with($currentDir, '..'))
        {
            $offsetToRoot = strlen('/..') + 1;
            $delimiterPosition = strrpos($currentDir, '\\', -$offsetToRoot);
            if($delimiterPosition === false){
                $currentDir = substr($currentDir, 0 ,$offsetToRoot + 1);
            }else{
                $currentDir = substr($currentDir, 0,$delimiterPosition);
            }
        }
        else if(str_ends_with($currentDir, '.'))
        {
            $currentDir = substr($currentDir, 0, -2);
        }
        return $currentDir;
    }

    private function isCurrentDirAllowedForAdmin(string $currentDir ): bool
    {
        $allowedDirs = $this->getAllowedDirs();
        $isAllowed = false;
        foreach ($allowedDirs as $allowedDir) {
            if (str_ends_with($allowedDir, $currentDir)) {
                $isAllowed = true;
                break;
            }
        }
        return $isAllowed;
    }

    public function getBreadcrumbs(?string $currentDir): array {
        $breadcrumbs = [];
        if ($currentDir) {
            $parts = explode('/', $currentDir);
            $currentPath = '';
            foreach ($parts as $part) {
                $currentPath .= $part . '/';
                $breadcrumbs[] = [
                    'name' => $part,
                    'path' => rtrim($currentPath, '/')
                ];
            }
        }
        return $breadcrumbs;
    }

    public function isPathAllowed(string $path): bool {
        $allowedDirs = $this->getAllowedDirs();
        $filePath = realpath(dirname($path));
        return in_array($filePath, $allowedDirs, true);
    }

    public function handleAdminPanelAccess(string $templatePath): string
    {
        $currentDir = $_GET['dir'] ?? 'public';
        try {
            $files = $this->getAllowedFiles($currentDir);
            $breadcrumbs = $this->getBreadcrumbs($currentDir);
            $allowedDirs = $this->getAllowedDirs();
            return $this->templateFacade->render($templatePath, [
                'currentDir' => $currentDir,
                'files' => $files,
                'breadcrumbs' => $breadcrumbs,
                'allowedDirs' => $allowedDirs
            ]);
        } catch (Exception $e) {
            Logger::error("Error: " . $e->getMessage());
            throw;
        }
    }

    /**
     * Проверяет, является ли файл изображением на основе расширения.
     *
     * @param string $path Путь к файлу.
     * @return bool Возвращает true, если файл имеет расширение, соответствующее изображению.
     */
    public function isImage(string $path): bool
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, $allowedExtensions, true);
    }


    public function normalizePath(string $path, string $toDir): string
    {
        $normalizedPath = str_replace('\\', '/', $path);
        $pos = strpos($normalizedPath, $toDir);

        if ($pos !== false) {
            $relativePath = substr($normalizedPath, $pos);
        } else {
            $relativePath = '';
        }
        return $relativePath;
    }
}