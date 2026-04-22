<?php

namespace Src\Security;

use Src\Request;
use Src\Session;

class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    public static function token(): string
    {
        $token = (string)Session::get(self::TOKEN_KEY);
        if ($token !== '') {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        Session::set(self::TOKEN_KEY, $token);

        return $token;
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }

    public static function refresh(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set(self::TOKEN_KEY, $token);

        return $token;
    }

    public static function validateRequest(Request $request): void
    {
        if (!$request->isStateChanging()) {
            return;
        }

        $token = (string)$request->get('_csrf', '');
        if (hash_equals(self::token(), $token)) {
            return;
        }

        self::abort();
    }

    private static function abort(): void
    {
        http_response_code(419);
        ?>
        <!doctype html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Сессия формы истекла</title>
            <style>
                body {
                    margin: 0;
                    min-height: 100vh;
                    display: grid;
                    place-items: center;
                    background: #f4f7fa;
                    color: #1f2937;
                    font: 16px/1.5 "Inter", "Segoe UI", sans-serif;
                }

                .error-card {
                    width: min(560px, calc(100vw - 32px));
                    padding: 28px;
                    border: 1px solid #d9e2ec;
                    border-radius: 18px;
                    background: #fff;
                    box-shadow: 0 20px 45px rgba(19, 43, 61, 0.08);
                }

                h1 {
                    margin: 0 0 10px;
                    font-size: 28px;
                    line-height: 1.1;
                }

                p {
                    margin: 0;
                    color: #6b7280;
                }
            </style>
        </head>
        <body>
        <main class="error-card">
            <h1>Сессия формы истекла</h1>
            <p>Повтори отправку формы со страницы приложения. CSRF-токен не прошёл проверку.</p>
        </main>
        </body>
        </html>
        <?php
        exit;
    }
}
