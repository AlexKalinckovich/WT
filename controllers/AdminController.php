<?php
declare(strict_types=1);
namespace Controller;
require_once __UTILS__ . '/SingletonTrait.php';

use Exception;

use utils\SingletonTrait;
use services\AdminService;


/**
 * @property $templateFacade
 */
class AdminController
{
    use SingletonTrait;
    private AdminService $adminService;

    protected function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    private function isAdminAuthorized(): bool
    {
        return isset($_SESSION['isAuthorized']) && $_SESSION['isAuthorized'];
    }


    public function checkPassword(): void
    {
        $username = $_GET['username'] ?? '';
        $password = $_GET['password'] ?? '';

        try {
            $isValid = $this->adminService->validateCredentials($username, $password);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $isValid = false;
            echo $errorMessage;
        }

        if ($isValid) {
            $_SESSION['isAuthorized'] = true;
        }

        header('Content-Type: application/json');
        echo json_encode(['valid' => $isValid]);
    }

    public function handleAdminPanel(): string
    {
        if($this->isAdminAuthorized()) {
            header('Location: /login');
            exit();
        }

        return $this->renderAdminPanel();
    }


    private function renderAdminPanel(): string
    {
        return $this->adminService->handleAdminPanelAccess(__TEMPLATES__ . '/admin_panel.html');
    }

    public function downloadFile(): void
    {
        $path = $_GET['path'] ?? '';
        if ($this->adminService->isPathAllowed($path)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($path) . '"');
            readfile($path);
            exit;
        }
        http_response_code(403);
    }

    public function getFileContent(): void
    {
        $path = $_GET['path'] ?? '';

        if (!$this->adminService->isPathAllowed($path)) {
            http_response_code(403);
            $this->jsonError('Доступ запрещён.');
        }

        if (!file_exists($path) || !is_readable($path)) {
            http_response_code(404);
            $this->jsonError('Файл не найден или недоступен для чтения.');
        }
        $isImage = $this->adminService->isImage($path);

        $content = file_get_contents($path);

        if ($content === false) {
            http_response_code(500);
            $this->jsonError('Не удалось прочитать файл.');
        }

        $encodedContent = $isImage ? base64_encode($content) : $content;

        $dirCut = '/public';
        $relativePath = $this->adminService->normalizePath($path,$dirCut);

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => true,
            'content' => $encodedContent,
            'mime'    => $isImage ? 'image' : 'text',
            'isImage' => $isImage,
            'src' => $relativePath
        ]);
        exit;
    }

    /**
     * Унифицированный JSON-ответ об ошибке.
     */
    private function jsonError(string $message): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }

    public function uploadFile(): void
    {
        $targetDir = $_POST['targetDir'] ?? '';
        $uploadPath = __DIR__ . '/../' . $targetDir . '/' . basename($_FILES['file']['name']);

        if ($this->adminService->isPathAllowed($uploadPath)) {
            move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function deleteFile(): void
    {
        $deletePath = $_GET['path'] ?? '';
        if($this->adminService->isPathAllowed($deletePath)) {
            $result = unlink($deletePath);
            $message = 'Error delete file: ' . $deletePath;
            echo json_encode(['success' => $result, 'message' => $message]);
        }
        else
        {
            echo json_encode(['success' => false,'message' => 'Access denied.']);
        }
    }
}