<?php

namespace Controller;

require_once __UTILS__ . '/SingletonTrait.php';

use Exception;
use services\RegistrationService;
use utils\SingletonTrait;

class RegistrationController
{
    use SingletonTrait;

    private RegistrationService $registrationService;

    protected function __construct(RegistrationService $registrationService){
        $this->registrationService = $registrationService;
    }

    public function handleRegistrationPage() : string{
        return $this->registrationService->handleRegistrationPage();
    }

    public function registerUser() : string{
        $input = json_decode(file_get_contents('php://input'), true);

        if (is_array($input)) {
            try {
                $result = $this->registrationService->registerUser($input);
            } catch (Exception $e) {
                http_response_code(400);
                $result = json_encode(['error' => $e->getMessage()]);
            }
        }else{
            http_response_code(400);
            $result = json_encode(['error' => 'Некорректный формат данных']);
        }
        return $result;
    }
}