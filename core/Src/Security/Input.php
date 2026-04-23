<?php

namespace Src\Security;

class Input
{
    public static function text(mixed $value, int $maxLength = 255): string
    {
        if (is_array($value) || is_object($value)) {
            return '';
        }

        $value = trim((string)$value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $value) ?? '';

        return mb_substr($value, 0, $maxLength);
    }

    public static function raw(mixed $value, int $maxLength = 255): string
    {
        if (is_array($value) || is_object($value)) {
            return '';
        }

        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', (string)$value) ?? '';

        return mb_substr($value, 0, $maxLength);
    }

    public static function search(mixed $value, int $maxLength = 120): string
    {
        return self::text($value, $maxLength);
    }

    public static function numericString(mixed $value): string
    {
        $value = trim((string)$value);

        return ctype_digit($value) ? $value : '';
    }

    public static function enum(mixed $value, array $allowed, string $default): string
    {
        $value = self::text($value, 64);

        return in_array($value, $allowed, true) ? $value : $default;
    }

    public static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
