<?php

namespace Src\Auth;

use Src\Security\Csrf;
use Src\Session;

class Auth
{
    //Свойство для хранения любого класса, реализующего интерфейс IdentityInterface
    private static IdentityInterface $user;
    private static $resolvedUser = null;
    private static bool $resolved = false;

    //Инициализация класса пользователя
    public static function init(IdentityInterface $user): void
    {
        self::$user = $user;
        self::$resolvedUser = null;
        self::$resolved = false;

        $currentUser = self::user();
        if ($currentUser) {
            self::$user = $currentUser;
        } else {
            Session::clear('id');
        }
    }

    //Вход пользователя по модели
    public static function login(IdentityInterface $user): void
    {
        Session::regenerate();
        Csrf::refresh();
        self::$user = $user;
        self::$resolvedUser = $user;
        self::$resolved = true;
        Session::set('id', self::$user->getId());
    }

    //Аутентификация пользователя и вход по учетным данным
    public static function attempt(array $credentials): bool
    {
        if (!isset($credentials['login'], $credentials['password'])) {
            return false;
        }

        if ($user = self::$user->attemptIdentity($credentials)) {
            self::login($user);
            return true;
        }
        return false;
    }

    //Возврат текущего аутентифицированного пользователя
    public static function user()
    {
        if (self::$resolved) {
            return self::$resolvedUser;
        }

        self::$resolved = true;

        $id = Session::get('id');
        if (!empty($id)) {
            $user = self::$user->findIdentity((int)$id);
            if ($user) {
                self::$resolvedUser = $user;
                return self::$resolvedUser;
            }

            Session::clear('id');
        }

        $token = self::bearerToken();
        if ($token === null || !method_exists(self::$user, 'findIdentityByToken')) {
            return null;
        }

        self::$resolvedUser = self::$user->findIdentityByToken($token);

        return self::$resolvedUser;
    }

    //Проверка является ли текущий пользователь аутентифицированным
    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function attemptApi(array $credentials): ?string
    {
        if (!isset($credentials['login'], $credentials['password'])) {
            return null;
        }

        $user = self::$user->attemptIdentity($credentials);
        if (!$user || !method_exists($user, 'issueApiToken')) {
            return null;
        }

        self::$resolvedUser = $user;
        self::$resolved = true;

        return $user->issueApiToken();
    }

    //Выход текущего пользователя
    public static function logout(): bool
    {
        Session::clear('id');
        Session::regenerate();
        Csrf::refresh();
        self::$resolvedUser = null;
        self::$resolved = false;
        return true;
    }

    private static function bearerToken(): ?string
    {
        $header = self::header('Authorization');
        if (!is_string($header)) {
            return null;
        }

        if (preg_match('/^\s*Bearer\s+(\S+)\s*$/i', $header, $matches) !== 1) {
            return null;
        }

        return trim((string)$matches[1]);
    }

    private static function header(string $name): ?string
    {
        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $headerName => $value) {
                if (strcasecmp((string)$headerName, $name) === 0) {
                    return is_string($value) ? $value : null;
                }
            }
        }

        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        $redirectServerKey = 'REDIRECT_' . $serverKey;
        if ($name === 'Content-Type') {
            return $_SERVER['CONTENT_TYPE'] ?? $_SERVER[$serverKey] ?? $_SERVER[$redirectServerKey] ?? null;
        }

        return $_SERVER[$serverKey] ?? $_SERVER[$redirectServerKey] ?? null;
    }

}
