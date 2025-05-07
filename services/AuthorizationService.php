<?php
namespace services;

use Exception;
use repositories\UserRepository;
use utils\Disposable;

define('__DEFAULT_NAME__','Гость');

class AuthorizationService
{
    private UserRepository $userRepository;
    private LoginService   $loginService;
    private string $userName = __DEFAULT_NAME__;
    public function getUserName(): string
    {
        return $this->userName;
    }

    public function isGuest(): bool
    {
        return $this->isGuest;
    }
    private bool $isGuest = true;

    public function __construct(UserRepository $userRepository, LoginService $loginService)
    {
        $this->userRepository = $userRepository;
        $this->loginService   = $loginService;
    }

    /**
     * Инициализирует сессию и возвращает информацию о текущем пользователе.
     *
     * @return void
     */
    public function authorize(): void
    {
        if(isset($_COOKIE[session_name()])) {
            session_start();
            $currentIpHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
            $currentUaHash = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');

            if (!isset($_SESSION['ip_hash'], $_SESSION['ua_hash'])) {
                $this->guestAuthorization();
            } else {
                $isIpAndUaMatch = $_SESSION['ip_hash'] === $currentIpHash &&
                    $_SESSION['ua_hash'] === $currentUaHash;
                if ($isIpAndUaMatch) {
                    $this->startSessionAuthorization();
                } else {
                    $this->guestAuthorization();
                }
            }
            $this->updateIpAndUaHash($currentIpHash, $currentUaHash);
        }else{
            $this->guestAuthorization();
        }
    }

    private function startSessionAuthorization(): void{
        if(isset($_SESSION['session_id'],$_SESSION['user_id'])){
            $userId    = $_SESSION['user_id'];
            $sessionId = $_SESSION['session_id'];
            $this->sessionAuthorization($sessionId, $userId);
        }else{
            $this->guestAuthorization();
        }
    }

    public function sessionAuthorization(string $sessionId, int $userId): void{
        $currentSessionId = session_id();
        if ($currentSessionId !== $sessionId) {
            $this->guestAuthorization();
        } else {
            $isTokenAuth = $this->tokenAuthorization();
            if(!$isTokenAuth) {
                $user = $this->userRepository->getById($userId);
                if ($user !== null) {
                    $this->userAuthorization($user->getUserName());
                } else {
                    $this->guestAuthorization();
                }
            }
        }
    }

    private function updateIpAndUaHash(string $currentIpHash,string $currentUaHash): void{
        $_SESSION['ip_hash'] = $currentIpHash;
        $_SESSION['ua_hash'] = $currentUaHash;
    }

    private function tokenAuthorization() : bool {
        $result = false;
        if (!empty($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $user = $this->userRepository->getByToken($token);
            if ($user !== null) {
                $this->userAuthorization($user->getUserName());
                $result = true;
            }
            setcookie('remember_token', '', time() - 3600, '/');
        }
        return $result;
    }

    private function guestAuthorization(): void{
        $this->loginService->logout();
        $this->isGuest = true;
        $this->userName = __DEFAULT_NAME__;
    }

    private function userAuthorization(string $userName): void
    {
        $this->isGuest = false;
        $this->userName = $userName;
    }

    /**
     * @throws Exception
     */
    public function confirmRegistration(int $userId): bool
    {

        $updateData = [
            'user_id'      => $userId,
            'user_name'    => '',
            'user_surname' => '',
            'user_email'   => '',
            'password_hash'=> '',
            'server_salt'  => '',
            'token'        => '',
            'isAuthorized'=> true,
        ];

        return $this->userRepository->update($updateData);
    }

}
