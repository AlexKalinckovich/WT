<?php

namespace Service;

use Exception;
use MyTemplate\TemplateFacade;

class AdminService
{
    private TemplateFacade $templateFacade;

    public function __construct(TemplateFacade $templateFacade)
    {
        $this->templateFacade = $templateFacade;
    }

    /**
     * @throws Exception
     */

    //############################## Functions for checking passwords to access #################################
    public function validateCredentials(string $username, string $password): bool
    {
        $htpasswdPath = __DIR__ . '\.htpasswd';
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
    //############################## Functions for checking passwords to access #################################


    //############################## Functions for files Showing #################################

    public function getAllowedDirs(): array
    {
        return [
            'views',
            'views/css',
            'views/js',
            'public/images'
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
                'fullPath' => realpath(__DIR__ . '/../' . $dir)
            ];
        }, $allowedDirs);
    }

    private function getCurrentDirFiles(string $currentDir): array
    {
        $fullPath = __DIR__ . '/../' . $currentDir;
        $items = scandir($fullPath);
        $files = [];
        foreach ($items as $item) {

            $itemPath = $currentDir . '/' . $item;
            $itemFullPath = $fullPath . '\\' . $item;

            $files[] = [
                'name' => $item,
                'is_dir' => is_dir($itemFullPath),
                'path' => $itemPath,
                'fullPath' => realpath($itemFullPath)
            ];
        }

        return $files;
    }

    private function handleCurrentAndParentPaths(string $currentDir): string
    {
        if (str_ends_with($currentDir, '..'))
        {
            $offsetToRoot = strlen('/..') + 1;
            $delimiterPosition = strrpos($currentDir, '/', -$offsetToRoot);
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
            if (str_ends_with($currentDir, $allowedDir)) {
                $isAllowed = true;
                break;
            }
        }
        return $isAllowed;
    }

    //############################## Functions for files Showing #################################
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
        $allowedPaths = [
            realpath(__DIR__ . '/../views'),
            realpath(__DIR__ . '/../views/css'),
            realpath(__DIR__ . '/../views/js'),
            realpath(__DIR__ . '/../views/images')
        ];

        $filePath = realpath(dirname($path));
        return in_array($filePath, $allowedPaths, true);
    }

    public function handleAdminPanelAccess(string $templatePath): string
    {
        $currentDir = $_GET['dir'] ?? 'views';
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
            die("Error: " . $e->getMessage());
        }
    }
}