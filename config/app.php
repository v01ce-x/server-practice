<?php

return [
    'auth' => \Src\Auth\Auth::class,
    'identity' => \Model\User::class,
    'routeMiddleware' => [
        'auth' => \Middlewares\AuthMiddleware::class,
        'guest' => \Middlewares\GuestMiddleware::class,
        'role' => \Middlewares\RoleMiddleware::class,
    ],
];
