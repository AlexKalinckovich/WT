<?php
namespace services;

require_once __UTILS__ . '/SingletonTrait.php';
require_once __REPOSITORIES__ . '/FoodRepository.php';
require_once __REPOSITORIES__ . '/UserRepository.php';
require_once __REPOSITORIES__ . '/OrderRepository.php';
require_once __UTILS__ . '/Data.php';

use Exception;
use exceptions\NotImplementedException;
use repositories\FoodRepository;
use repositories\UserRepository;
use repositories\OrderRepository;
use utils\Logger;
use utils\SingletonTrait;
use MyTemplate\TemplateFacade;

class MainService
{
    use SingletonTrait;

    private TemplateFacade   $templateFacade;
    private FoodRepository   $foodRepository;
    private UserRepository   $userRepository;
    private OrderRepository  $orderRepository;

    private AuthorizationService $authorizationService;
    protected function __construct(
        TemplateFacade   $templateFacade,
        FoodRepository   $foodRepository,
        UserRepository   $userRepository,
        OrderRepository  $orderRepository,
        AuthorizationService $authorizationService
    ) {
        $this->templateFacade  = $templateFacade;
        $this->foodRepository  = $foodRepository;
        $this->userRepository  = $userRepository;
        $this->orderRepository = $orderRepository;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @throws NotImplementedException
     */
    public function handleMainPage(): string
    {

        // 1) menuItems
        $food    = $this->foodRepository->getAll();
        $menuItems = [];
        foreach ($food as $pizza) {
            $menuItems[] = $pizza->toArray();
        }

        // 2) users
        $users     = $this->userRepository->getAll();
        $userList  = [];
        foreach ($users as $user) {
            $userList[] = [
                'userName'    => $user->getUserName(),
                'userSurname' => $user->getUserSurname(),
            ];
        }

        // 3) orders
        $orders     = $this->orderRepository->getAll();
        $orderList  = [];
        foreach ($orders as $order) {
            $orderList[] = [
                'userName' => $order->getUserName(),
                'foodName' => $order->getFoodName(),
            ];
        }

        $isGuest  = $this->authorizationService->isGuest();
        $userName = $this->authorizationService->getUserName();

        try {
            $result = $this->templateFacade->render(
                __TEMPLATES__ . '/main_page.html',
                [
                    'isGuest'   => $isGuest,
                    'userName'  => $userName,
                    'menuItems' => $menuItems,
                    'users'     => $userList,
                    'orders'    => $orderList,
                ]
            );
        } catch (Exception $e) {
            Logger::error($e->getMessage(),[$e]);
            $result = $e->getMessage();
        }
        return $result;
    }

}
