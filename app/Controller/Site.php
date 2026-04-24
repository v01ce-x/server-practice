<?php

namespace Controller;

use Src\Request;
use Src\View;

class Site
{
    // Совместимость со старым шаблоном проекта: перенаправляем на актуальные маршруты.
    public function index(Request $request): void
    {
        app()->route->redirect('/');
    }

    public function hello(): void
    {
        app()->route->redirect('/');
    }

    public function signup(Request $request): void
    {
        app()->route->redirect('/login');
    }

    public function login(Request $request): View|string
    {
        return (new Auth())->login($request);
    }

    public function logout(): void
    {
        (new Auth())->logout();
    }
}
