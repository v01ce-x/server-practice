<?php

namespace Controller;

use Model\Department;
use Model\Phone;
use Model\Role;
use Model\Room;
use Model\Subscriber;
use Model\User;
use Src\Auth\Auth as AuthService;
use Src\Request;
use Src\Session;
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

    public function login(Request $request): string
    {
        $authRole = $request->get('auth_role', User::ROLE_SYSTEM_ADMIN);

        if ($request->isMethod('POST')) {
            if (AuthService::attempt([
                'login' => trim((string)$request->get('login')),
                'password' => (string)$request->get('password'),
                'auth_role' => $authRole,
            ])) {
                $user = AuthService::user();
                Session::flash('Добро пожаловать в систему внутренней телефонной связи.');
                app()->route->redirect($user->isAdministrator() ? '/admins' : '/dashboard');
            }

            return new View('site.login', $this->loginViewData([
                'authPage' => true,
                'authRole' => $authRole,
                'message' => 'Неверный логин, пароль или выбранная роль.',
                'messageType' => 'error',
                'formData' => [
                    'login' => trim((string)$request->get('login')),
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
        Role::idFor(User::ROLE_ADMINISTRATOR);
        Role::idFor(User::ROLE_SYSTEM_ADMIN);
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
