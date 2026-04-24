<?php

use Src\Route;

Route::add('GET', '', [Controller\Api::class, 'index']);
Route::add('GET', '/', [Controller\Api::class, 'index']);
Route::add('POST', '/login', [Controller\Api::class, 'login']);
Route::add('GET', '/subscribers', [Controller\Api::class, 'subscribers'])
    ->middleware('auth', 'role:system_admin');
Route::add('POST', '/echo', [Controller\Api::class, 'echo']);
