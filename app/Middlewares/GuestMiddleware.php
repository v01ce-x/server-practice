<?php

namespace Middlewares;

use Model\User;
use Src\Auth\Auth;
use Src\Request;

class GuestMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            return;
        }

        /** @var User $user */
        $user = Auth::user();
        app()->route->redirect($user->isAdministrator() ? '/admins' : '/dashboard');
    }
}
