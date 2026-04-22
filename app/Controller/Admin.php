<?php

namespace Controller;

use Model\Role;
use Model\User;
use Src\Request;
use Src\Security\Input;
use Src\Session;
use Src\Validator\Forms\CredentialsFormValidator;
use Src\View;
use Throwable;

class Admin
{
    public function index(Request $request): string
    {
        $queryText = Input::search($request->get('q', ''));
        $escapedQueryText = Input::escapeLike($queryText);
        $errors = [];
        $showCreateForm = $request->get('create') === '1';
        $formData = [
            'login' => Input::text($request->get('login', ''), 120),
            'password' => Input::raw($request->get('password', ''), 255),
        ];

        if ($request->isMethod('POST')) {
            $showCreateForm = true;
            $validator = new CredentialsFormValidator($formData);

            $errors = $validator->messages();

            if ($errors === []) {
                try {
                    User::query()->create([
                        'login' => $formData['login'],
                        'password' => $formData['password'],
                        'role_id' => Role::idFor(User::ROLE_SYSTEM_ADMIN),
                    ]);

                    Session::flash('Системный администратор добавлен.');
                    app()->route->redirect('/admins');
                } catch (Throwable $exception) {
                    $errors[] = $exception->getMessage();
                }
            }
        }

        return new View('site.admins', [
            'activeMenu' => 'admins',
            'query' => $queryText,
            'admins' => User::query()
                ->with('roleRelation')
                ->whereHas('roleRelation', function ($query) {
                    $query->where('role', User::ROLE_SYSTEM_ADMIN);
                })
                ->when($queryText !== '', function ($query) use ($escapedQueryText) {
                    $query->where('login', 'like', "%{$escapedQueryText}%");
                })
                ->orderBy('login')
                ->get(),
            'policies' => [
                'Только администратор системы может добавлять новых системных администраторов.',
                'Системный администратор работает с абонентами, помещениями, подразделениями и телефонами.',
            ],
            'showCreateForm' => $showCreateForm,
            'createErrors' => $errors,
            'createData' => $formData,
        ]);
    }
}
