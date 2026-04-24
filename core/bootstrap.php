<?php
use Src\Security\Csrf;
use function Collect\collection;

const DIR_CONFIG = '/../config';

if (!function_exists('getConfigs')) {
    function getConfigs(string $path = DIR_CONFIG): array
    {
        $settings = [];

        collection(scandir(__DIR__ . $path) ?: [])
            ->each(static function (string $file) use ($path, &$settings): void {
                $name = explode('.', $file)[0];

                if ($name !== '') {
                    $settings[$name] = include __DIR__ . "$path/$file";
                }
            });

        return $settings;
    }
}

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Src\Application(new Src\Settings(getConfigs()));

if (!function_exists('app')) {
    function app()
    {
        global $app;
        return $app;
    }
}

if (!function_exists('url')) {
    function url(string $path): string
    {
        return app()->route->getUrl($path);
    }
}

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

return $app;
