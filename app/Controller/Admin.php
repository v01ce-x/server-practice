<?php

namespace Controller;

use Model\Role;
use Model\User;
use Src\Request;
use Src\Session;
use Src\View;
use Throwable;

class Admin
{
    public function index(Request $request): string
    {
        $queryText = trim((string)$request->get('q', ''));
        $errors = [];
        $showCreateForm = (bool)$request->get('create');
        $formData = [
            'login' => trim((string)$request->get('login', '')),
            'password' => trim((string)$request->get('password', '')),
        ];

        if ($request->isMethod('POST')) {
            $showCreateForm = true;
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

        return new View('site.admins', [
            'activeMenu' => 'admins',
            'query' => $queryText,
            'admins' => User::query()
                ->with('roleRelation')
                ->whereHas('roleRelation', function ($query) {
                    $query->where('role', User::ROLE_SYSTEM_ADMIN);
                })
                ->when($queryText !== '', function ($query) use ($queryText) {
                    $query->where('login', 'like', "%{$queryText}%");
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
