<?php

namespace Src;

class Session
{
    public static function set($name, $value): void
    {
        $_SESSION[$name] = $value;
    }

    public static function get($name)
    {
        return $_SESSION[$name] ?? null;
    }

    public static function clear($name)
    {
        unset($_SESSION[$name]);
    }

    public static function has(string $name): bool
    {
        return array_key_exists($name, $_SESSION);
    }

    public static function pull(string $name, $default = null)
    {
        $value = self::get($name) ?? $default;
        self::clear($name);
        return $value;
    }

    public static function flash(string $message, string $type = 'success'): void
    {
        self::set('_flash', [
            'message' => $message,
            'type' => $type,
        ]);
    }
}
