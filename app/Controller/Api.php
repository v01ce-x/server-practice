<?php

namespace Controller;

use Model\Department;
use Model\Subscriber;
use Model\User;
use Src\Auth\Auth as AuthService;
use Src\Request;
use Src\Security\Input;
use Src\Validator\Forms\CredentialsFormValidator;
use Src\View;

class Api
{
    public function index(): void
    {
        (new View())->toJSON([
            'status' => 'ok',
            'message' => 'API is available.',
            'endpoints' => [
                'GET /api',
                'POST /api/login',
                'GET /api/subscribers',
                'POST /api/echo',
            ],
        ]);
    }

    public function login(Request $request): void
    {
        $authRole = Input::enum(
            $request->get('auth_role', ''),
            [User::ROLE_SYSTEM_ADMIN, User::ROLE_ADMINISTRATOR],
            ''
        );
        $credentials = [
            'login' => Input::text($request->get('login', ''), 120),
            'password' => Input::raw($request->get('password', ''), 255),
        ];

        $validator = new CredentialsFormValidator($credentials);
        $errors = $validator->messages();
        if ($errors !== []) {
            (new View())->toJSON([
                'message' => 'Ошибка валидации данных для входа.',
                'errors' => $errors,
            ], 422);
        }

        if ($authRole !== '') {
            $credentials['auth_role'] = $authRole;
        }

        $token = AuthService::attemptApi($credentials);
        if ($token === null) {
            (new View())->toJSON([
                'message' => 'Неверный логин, пароль или роль.',
            ], 401);
        }

        /** @var User $user */
        $user = AuthService::user();

        (new View())->toJSON([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'login' => $user->login,
                'role' => $user->role,
                'role_label' => $user->getRoleLabel(),
            ],
        ]);
    }

    public function echo(Request $request): void
    {
        (new View())->toJSON($request->all());
    }

    public function subscribers(Request $request): void
    {
        $departmentFilter = Input::numericString($request->get('department', ''));
        $stateFilter = Input::enum(
            $request->get('state', 'all'),
            ['all', 'with_phone', 'without_phone'],
            'all'
        );
        $queryText = Input::search($request->get('q', ''));
        $escapedQueryText = Input::escapeLike($queryText);

        $subscribers = Subscriber::query()
            ->with(['department', 'phone.room'])
            ->when($departmentFilter !== '', function ($query) use ($departmentFilter) {
                $query->where('division_id', (int)$departmentFilter);
            })
            ->when($stateFilter === 'with_phone', function ($query) {
                $query->has('phone');
            })
            ->when($stateFilter === 'without_phone', function ($query) {
                $query->doesntHave('phone');
            })
            ->when($queryText !== '', function ($query) use ($escapedQueryText) {
                $query->where(function ($inner) use ($escapedQueryText) {
                    $inner
                        ->where('last_name', 'like', "%{$escapedQueryText}%")
                        ->orWhere('first_name', 'like', "%{$escapedQueryText}%")
                        ->orWhere('patronymic', 'like', "%{$escapedQueryText}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        (new View())->toJSON([
            'filters' => [
                'query' => $queryText,
                'department' => $departmentFilter === '' ? null : (int)$departmentFilter,
                'state' => $stateFilter,
            ],
            'departments' => Department::query()
                ->orderBy('name')
                ->get()
                ->map(static fn (Department $department) => [
                    'id' => $department->id,
                    'name' => $department->name,
                ])
                ->all(),
            'count' => $subscribers->count(),
            'items' => $subscribers->map(static fn (Subscriber $subscriber) => [
                'id' => $subscriber->id,
                'last_name' => $subscriber->last_name,
                'first_name' => $subscriber->first_name,
                'middle_name' => $subscriber->middle_name,
                'full_name' => $subscriber->full_name,
                'birth_date' => $subscriber->birthdate,
                'birth_date_formatted' => $subscriber->birth_date_formatted,
                'department' => $subscriber->department ? [
                    'id' => $subscriber->department->id,
                    'name' => $subscriber->department->name,
                ] : null,
                'phone' => $subscriber->phone ? [
                    'id' => $subscriber->phone->id,
                    'number' => $subscriber->phone->number,
                    'room' => $subscriber->phone->room ? [
                        'id' => $subscriber->phone->room->id,
                        'name' => $subscriber->phone->room->name,
                        'type' => $subscriber->phone->room->type,
                    ] : null,
                ] : null,
            ])->all(),
        ]);
    }
}
