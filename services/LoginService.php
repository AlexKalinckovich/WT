<?php
namespace services;

require_once __UTILS__ . '/SingletonTrait.php';
require_once __REPOSITORIES__ . '/UserRepository.php';

use DateTime;
use Exception;
use repositories\UserRepository;
use utils\Logger;
use utils\SingletonTrait;
use MyTemplate\TemplateFacade;

class LoginService
{
    use SingletonTrait;

    private TemplateFacade   $templateFacade;
    private UserRepository   $userRepository;
    private MailService      $mailService;

    protected function __construct(
        UserRepository   $userRepository,
        TemplateFacade   $templateFacade,
        MailService      $mailService
    ) {
        $this->userRepository  = $userRepository;
        $this->templateFacade  = $templateFacade;
        $this->mailService    = $mailService;
    }

    public function handleLoginPage(): string
    {
        $result = '';
        $type = '__login';
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

    /**
     * @param array{email:string,passwordHash:string,rememberMe:bool} $input
     * @return bool
     * @throws Exception
     */
    public function authenticate(array $input): bool
    {
        if (empty($input['email']) || empty($input['passwordHash'])) {
            throw new Exception('Email или пароль не заполнены');
        }

        $user = $this->userRepository->getByEmail($input['email']);
        if ($user === null) {
            throw new Exception('Пользователь не найден');
        }

        $isAuthorized = $user->getIsAuthorized();
        if(!$isAuthorized) {
            throw new Exception("Пользователь не авторизован");
        }

        $serverSalt  = $user->getSalt();
        $storedHash  = $user->getPasswordHash();

        $clientHash  = $input['passwordHash'];
        $finalHash   = hash('sha256', $clientHash . $serverSalt);

        if (!hash_equals($storedHash, $finalHash)) {
            throw new Exception('Неверный пароль');
        }
        $userId = $user->getUserId();

        if (!empty($input['rememberMe'])) {
            $token = bin2hex(random_bytes(16));
            $this->userRepository->updateRememberToken($userId, $token);
            setcookie('remember_token', $token, time() + 60 * 60 * 24 * 30, '/');
        }

        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }

        $_SESSION['user_id'] = $userId;
        $_SESSION['session_id'] = session_id();
        $_SESSION['ip_hash'] = hash('sha256', $_SERVER['REMOTE_ADDR']);
        $_SESSION['ua_hash'] = hash('sha256', $_SERVER['HTTP_USER_AGENT']);

        $unixTime  = time();
        $userName  = $user->getUserName();
        $userEmail = $user->getUserEmail();
        $this->mailService->sendLoginNotification($userName, $userEmail, $unixTime);

        Logger::info('Пользователь авторизован', ['id' => $user->getUserId()]);
        return true;
    }

    public function logout(): void
    {

        if (!empty($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $user = $this->userRepository->getByToken($token);
            if ($user !== null) {
                $this->userRepository->updateRememberToken($user->getUserId, '');
            }
            setcookie('remember_token', '', time() - 3600, '/');
        }

        setcookie('remember_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 3600,
                'path'     => $params["path"],
                'domain'   => $params["domain"],
                'secure'   => $params["secure"],
                'httponly' => $params["httponly"],
                'samesite' => $params["samesite"] ?? 'Lax',
            ]);
        }

        Logger::info('Пользователь вышел из системы', []);
    }
}
