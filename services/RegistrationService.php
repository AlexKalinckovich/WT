<?php
namespace services;

require_once __UTILS__ . '/SingletonTrait.php';
require_once __REPOSITORIES__ . '/UserRepository.php';

use Exception;
use repositories\UserRepository;
use utils\Logger;
use utils\SingletonTrait;
use MyTemplate\TemplateFacade;

class RegistrationService
{
    use SingletonTrait;

    private TemplateFacade   $templateFacade;
    private UserRepository   $userRepository;

    protected function __construct(
        UserRepository   $userRepository,
        TemplateFacade   $templateFacade,
    ) {
        $this->userRepository  = $userRepository;
        $this->templateFacade  = $templateFacade;
    }


    /**
     * @param array $input ['userName', 'userSurname', 'email', 'passwordHash', 'rememberMe']
     * @return bool
     * @throws Exception
     */
    public function registerUser(array $input): bool
    {
        foreach (['userName','email','passwordHash'] as $field) {
            if (empty($input[$field])) {
                throw new Exception("Поле $field обязательно.");
            }
        }

        $users = $this->userRepository->getByEmail($input['email']);
        if(!empty($users)){
            throw new Exception("Пользователь с данным email уже зарегестрирован!");
        }

        $serverSalt = bin2hex(random_bytes(16));

        $clientHash  = $input['passwordHash'];
        $finalHash   = hash('sha256', $clientHash . $serverSalt);

        $userData = [
            'user_name'     => $input['userName'],
            'user_surname'  => $input['userSurname'] ?? '',
            'user_email'    => $input['email'],
            'server_salt'   => $serverSalt,
            'password_hash' => $finalHash,
            'token'         => '',
        ];

        $userId = -1;
        $result = $this->userRepository->create($userData, $userId);

        if (!$result) {
            Logger::error('Не удалось сохранить пользователя', $userData);
            throw new Exception('Ошибка при регистрации.');
        }

        $_SESSION['userId']       = $userId;
        $_SESSION['salt']         = $userData['server_salt'];
        $_SESSION['passwordHash'] = $userData['password_hash'];

        Logger::info('Пользователь успешно зарегистрирован', ['email' => $input['email']]);
        return true;
    }

    public function handleRegistrationPage(): string
    {
        $result = '';
        $type = 'Регистрация';
        try {
            $main = $this->templateFacade->render(__TEMPLATES__ . '/registerPages/registration.html', []);
        } catch (Exception $e) {
            $main = $e->getMessage();
        }
        try {
            $result = $this->templateFacade->render(
                __TEMPLATES__ . '/registerPages/layout.html',
                [
                    'type'      => $type,
                    'main'      => $main
                ]
            );
        } catch (Exception $e) {
            Logger::error("Ошибка рендеринга шаблона: " . $e->getMessage());
        }
        return $result;
    }

    public function handleLoginPage(): string
    {
        $result = '';
        $type = 'Логин';
        try {
            $main = $this->templateFacade->render(__TEMPLATES__ . '/registerPages/login.html', []);
        } catch (Exception $e) {
            $main = $e->getMessage();
        }
        try {
            $result = $this->templateFacade->render(
                __TEMPLATES__ . '/registerPages/layout.html',
                [
                    'type'      => $type,
                    'main'      => $main
                ]
            );
        } catch (Exception $e) {
            Logger::error("Ошибка рендеринга шаблона: " . $e->getMessage());
        }
        return $result;
    }
}
