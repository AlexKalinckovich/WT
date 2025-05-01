<?php
namespace services;

use repositories\UserRepository;

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
        $currentIpHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
        $currentUaHash = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');

        if(isset($_SESSION["userId"], $_SESSION['salt'], $_SESSION['passwordHash'])){

            $userId = $_SESSION["userId"];
            $salt = $_SESSION["salt"];
            $passwordHash = $_SESSION["passwordHash"];

            unset($_SESSION["userId"], $_SESSION["salt"], $_SESSION["passwordHash"]);
            $this->loginAuthorization($userId, $salt, $passwordHash);
            $this->updateIpAndUaHash($currentIpHash, $currentUaHash);
            return;
        }

        if (isset($_SESSION['ip_hash'], $_SESSION['ua_hash'])) {
            $this->tokenAuthorization($currentIpHash, $currentUaHash);
        }else{
            $this->updateIpAndUaHash($currentIpHash, $currentUaHash);
            $this->guestAuthorization();
        }
    }

    private function updateIpAndUaHash(string $currentIpHash,string $currentUaHash): void{
        $_SESSION['ip_hash'] = $currentIpHash;
        $_SESSION['ua_hash'] = $currentUaHash;
    }

    private function tokenAuthorization(string $currentIpHash, string $currentUaHash) : void {
        if ($_SESSION['ip_hash'] !== $currentIpHash || $_SESSION['ua_hash'] !== $currentUaHash) {
            $this->updateIpAndUaHash($currentIpHash, $currentUaHash);
            $this->loginService->logout();
        }else{
            if (!empty($_COOKIE['remember_token'])) {
                $token = $_COOKIE['remember_token'];
                $user = $this->userRepository->getByToken($token);
                if ($user !== null) {
                    $this->userAuthorization($user->getUserName());
                }
                setcookie('remember_token', '', time() - 3600, '/');
            }
        }
    }

    private function loginAuthorization(int $userId, string $salt, string $passwordHash): void{
        $user = $this->userRepository->getById($userId);
        if($user === null){
            $this->guestAuthorization();
        }else{
            $isSaltMatch =             $salt === $user->getSalt();
            $isPasswordMatch = $passwordHash === $user->getPasswordHash();

            if($isSaltMatch && $isPasswordMatch){
                $this->userAuthorization($user->getUserName());
            }else{
                $this->guestAuthorization();
            }
        }
    }

    private function guestAuthorization(): void{
        $this->isGuest = true;
        $this->userName = __DEFAULT_NAME__;
    }

    private function userAuthorization(string $userName): void
    {
        $this->isGuest = false;
        $this->userName = $userName;
    }

}
