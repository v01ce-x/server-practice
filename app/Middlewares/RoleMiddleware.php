<?php

namespace Middlewares;

use Model\User;
use Src\Auth\Auth;
use Src\Request;
use Src\Session;
use Src\View;

class RoleMiddleware
{
    public function handle(Request $request, ?string $roles = null): void
    {
        if (!Auth::check()) {
            if ($request->isApi()) {
                (new View())->toJSON([
                    'message' => 'Требуется аутентификация по Bearer token.',
                ], 401);
            }

            app()->route->redirect('/login');
        }

        /** @var User $user */
        $user = Auth::user();
        $allowedRoles = array_filter(array_map('trim', explode(',', (string)$roles)));

        if ($allowedRoles === [] || in_array($user->role, $allowedRoles, true)) {
            return;
        }

        if ($request->isApi()) {
            (new View())->toJSON([
                'message' => 'Недостаточно прав для выполнения API-запроса.',
            ], 403);
        }

        Session::flash('Недостаточно прав для доступа к выбранному разделу.', 'error');
        app()->route->redirect($user->isAdministrator() ? '/admins' : '/dashboard');
    }
}
