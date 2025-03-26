<?php

namespace Controller;

use Exception;
use Service\AdminService;

/**
 * @property $templateFacade
 */
class AdminController
{
    private AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    private function isAdminAuthorized(): bool
    {
        return isset($_SESSION['isAuthorized']) && $_SESSION['isAuthorized'];
    }

    public function handleLogin(): void
    {
        if($this->isAdminAuthorized())
        {
            echo $this->renderAdminPanel();
        }
        else
        {
            include __DIR__ . '/../templates/login.html';
        }
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

    public function handleAdminPanel(): void
    {
        if($this->isAdminAuthorized()) {
            header('Location: /login');
            exit();
        }

        echo $this->renderAdminPanel();
    }


    private function renderAdminPanel(): string
    {
        return $this->adminService->handleAdminPanelAccess('templates/admin_panel.html');
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
        if ($this->adminService->isPathAllowed($path)) {
            header('Content-Type: application/json; charset=UTF-8');
            json_encode('success:true');
            $response = array(
                'success' => true,
                'content' => file_get_contents($path)
            );
            echo json_encode($response);
            exit;
        }
        http_response_code(403);
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