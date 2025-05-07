<?php

declare(strict_types=1);
namespace Controller;

use services\AuthorizationService;
use utils\SingletonTrait;
use Exception;

require_once __UTILS__ . '/SingletonTrait.php';
require_once __REPOSITORIES__ . '/UserRepository.php';

/**
 * Контроллер для подтверждения регистрации пользователя по ссылке из email.
 */
class AuthorizationController
{
    use SingletonTrait;

    private AuthorizationService $authorizationService;

    protected function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * Подтверждает регистрацию текущего пользователя.
     * Ожидает в сессии наличие 'user_id' и валидный токен подтверждения.
     * После подтверждения помечает пользователя как авторизованного.
     * @throws Exception при отсутствии данных или ошибке БД
     */
    public function confirmRegistration(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            throw new Exception('Невозможно подтвердить регистрацию: нет информации о пользователе в сессии.');
        }

        $result = $this->authorizationService->confirmRegistration($_SESSION['user_id']);
        if (!$result) {
            throw new Exception('Ошибка при обновлении статуса авторизации пользователя.');
        }

        header('Location: /');
        exit;
    }
}
