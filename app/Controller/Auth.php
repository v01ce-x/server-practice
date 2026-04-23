<?php

namespace Controller;

use Model\Department;
use Model\Phone;
use Model\Room;
use Model\Subscriber;
use Model\User;
use Src\Auth\Auth as AuthService;
use Src\Request;
use Src\Security\Input;
use Src\Session;
use Src\Validator\Forms\CredentialsFormValidator;
use Src\View;

class Auth
{
    public function home(): void
    {
        if (!AuthService::check()) {
            app()->route->redirect('/login');
        }

        /** @var User $user */
        $user = AuthService::user();
        app()->route->redirect($user->isAdministrator() ? '/admins' : '/dashboard');
    }

    public function login(Request $request): View|string
    {
        $authRole = Input::enum(
            $request->get('auth_role', User::ROLE_SYSTEM_ADMIN),
            [User::ROLE_SYSTEM_ADMIN, User::ROLE_ADMINISTRATOR],
            User::ROLE_SYSTEM_ADMIN
        );
        $formData = [
            'login' => Input::text($request->get('login', ''), 120),
            'password' => Input::raw($request->get('password', ''), 255),
        ];

        if ($request->isMethod('POST')) {
            $validator = new CredentialsFormValidator($formData);

            if ($validator->fails()) {
                return new View('site.login', $this->loginViewData([
                    'authPage' => true,
                    'authRole' => $authRole,
                    'messages' => $validator->messages(),
                    'messageType' => 'error',
                    'formData' => [
                        'login' => $formData['login'],
                    ],
                ]));
            }

            if (AuthService::attempt([
                'login' => $formData['login'],
                'password' => $formData['password'],
                'auth_role' => $authRole,
            ])) {
                $user = AuthService::user();
                Session::flash('Добро пожаловать в систему внутренней телефонной связи.');
                app()->route->redirect($user->isAdministrator() ? '/admins' : '/dashboard');
            }

            return new View('site.login', $this->loginViewData([
                'authPage' => true,
                'authRole' => $authRole,
                'messages' => ['Неверный логин, пароль или выбранная роль.'],
                'messageType' => 'error',
                'formData' => [
                    'login' => $formData['login'],
                ],
            ]));
        }

        return new View('site.login', $this->loginViewData([
            'authPage' => true,
            'authRole' => $authRole,
            'formData' => [
                'login' => '',
            ],
        ]));
    }

    public function logout(): void
    {
        AuthService::logout();
        app()->route->redirect('/login');
    }

    private function loginViewData(array $extra = []): array
    {
        $adminCount = User::query()->count();

        return array_merge([
            'loginStats' => [
                ['value' => Subscriber::query()->count(), 'label' => 'Абонентов'],
                ['value' => Phone::query()->count(), 'label' => 'Номеров'],
                ['value' => Room::query()->count(), 'label' => 'Помещений'],
                ['value' => Department::query()->count(), 'label' => 'Подразделений'],
            ],
            'setupHint' => $adminCount === 0
                ? 'В таблице admins пока нет учетных записей. Создайте администратора через phpMyAdmin на localhost:8081 и укажите role_id = 1.'
                : null,
        ], $extra);
    }
}
