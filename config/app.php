<?php

return [
    'auth' => \Src\Auth\Auth::class,
    'identity' => \Model\User::class,
    'validators' => [
        'integer' => \Src\Validator\Rules\IntegerValidator::class,
        'max' => \Src\Validator\Rules\MaxLengthValidator::class,
        'plainText' => \Src\Validator\Rules\PlainTextValidator::class,
        'phone' => \Src\Validator\Rules\PhoneValidator::class,
        'required' => \Src\Validator\Rules\RequiredValidator::class,
        'russianDate' => \Src\Validator\Rules\RussianDateValidator::class,
    ],
    'routeMiddleware' => [
        'auth' => \Middlewares\AuthMiddleware::class,
        'guest' => \Middlewares\GuestMiddleware::class,
        'role' => \Middlewares\RoleMiddleware::class,
    ],
    //Классы провайдеров
    'providers' => [
        'kernel' => \Providers\KernelProvider::class,
        'route' => \Providers\RouteProvider::class,
        'db' => \Providers\DBProvider::class,
        'auth' => \Providers\AuthProvider::class,
    ],
    'routeAppMiddleware' => [
       'csrf' => \Middlewares\CSRFMiddleware::class,
       'specialChars' => \Middlewares\SpecialCharsMiddleware::class,
       'trim' => \Middlewares\TrimMiddleware::class,
       'json' => \Middlewares\JSONMiddleware::class,
],
];
