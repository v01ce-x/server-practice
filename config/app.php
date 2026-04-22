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
];
