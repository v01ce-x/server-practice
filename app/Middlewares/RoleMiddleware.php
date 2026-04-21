<?php

namespace Middlewares;

use Model\User;
use Src\Auth\Auth;
use Src\Request;
use Src\Session;

class RoleMiddleware
{
    public function handle(Request $request, ?string $roles = null): void
    {
        if (!Auth::check()) {
            app()->route->redirect('/login');
        }

        /** @var User $user */
        $user = Auth::user();
        $allowedRoles = array_filter(array_map('trim', explode(',', (string)$roles)));

        if ($allowedRoles === [] || in_array($user->role, $allowedRoles, true)) {
            return;
        }

        Session::flash('Недостаточно прав для доступа к выбранному разделу.', 'error');
        app()->route->redirect($user->isAdministrator() ? '/admins' : '/dashboard');
    }
}
