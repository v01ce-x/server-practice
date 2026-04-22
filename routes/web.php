<?php

use Src\Route;

Route::add('GET', '/', [Controller\Auth::class, 'home']);

Route::add(['GET', 'POST'], '/login', [Controller\Auth::class, 'login'])
    ->middleware('guest');
Route::add('POST', '/logout', [Controller\Auth::class, 'logout'])
    ->middleware('auth');

Route::add('GET', '/dashboard', [Controller\Dashboard::class, 'index'])
    ->middleware('auth', 'role:system_admin,administrator');
Route::add('POST', '/profile/avatar', [Controller\Dashboard::class, 'uploadAvatar'])
    ->middleware('auth');

Route::add(['GET', 'POST'], '/subscribers', [Controller\Subscriber::class, 'index'])
    ->middleware('auth', 'role:system_admin');
Route::add(['GET', 'POST'], '/subscribers/{id:\d+}', [Controller\Subscriber::class, 'show'])
    ->middleware('auth', 'role:system_admin');

Route::add(['GET', 'POST'], '/phones', [Controller\Phone::class, 'index'])
    ->middleware('auth', 'role:system_admin');
Route::add(['GET', 'POST'], '/phones/assign/{id:\d+}', [Controller\Phone::class, 'assign'])
    ->middleware('auth', 'role:system_admin');

Route::add(['GET', 'POST'], '/directories', [Controller\Directory::class, 'index'])
    ->middleware('auth', 'role:system_admin');

Route::add('GET', '/reports/export', [Controller\Report::class, 'export'])
    ->middleware('auth', 'role:system_admin');
Route::add('GET', '/reports', [Controller\Report::class, 'index'])
    ->middleware('auth', 'role:system_admin');

Route::add(['GET', 'POST'], '/admins', [Controller\Admin::class, 'index'])
    ->middleware('auth', 'role:administrator');
