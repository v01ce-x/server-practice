<?php

namespace Middlewares;

use Src\Auth\Auth;
use Src\Request;
use Src\View;

class AuthMiddleware
{
    public function handle(Request $request)
    {
        if (!Auth::check()) {
            if ($request->isApi()) {
                (new View())->toJSON([
                    'message' => 'Требуется аутентификация по Bearer token.',
                ], 401);
            }

            app()->route->redirect('/login');
        }
    }
}
