<?php

namespace Controller;

require_once __UTILS__ . '/SingletonTrait.php';

use Exception;
use services\LoginService;
use utils\SingletonTrait;

class LoginController
{
    use SingletonTrait;

    private LoginService $loginService;

    protected function __construct(LoginService $loginService){
        $this->loginService = $loginService;
    }

    public function handleLoginPage() : string{
        return $this->loginService->handleLoginPage();
    }

    public function handleAuthorization(): void
    {

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Некорректный формат данных']);
            exit;
        }

        try {
            $ok = $this->loginService->authenticate($input);

            header('Content-Type: application/json');
            if($ok) {
                $result = json_encode(['success' => true]);
            }else{
                $result = json_encode(['success' => false]);
            }
        } catch (Exception $e) {
            http_response_code(401);
            $result = json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        echo $result;
        exit;
    }

    public function logout(): void
    {
        header('Content-Type: application/json');
        try {
            $this->loginService->logout();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

}