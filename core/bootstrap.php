<?php
const DIR_CONFIG = '/../config';

if (!function_exists('getConfigs')) {
    function getConfigs(string $path = DIR_CONFIG): array
    {
        $settings = [];
        foreach (scandir(__DIR__ . $path) as $file) {
            $name = explode('.', $file)[0];
            if (!empty($name)) {
                $settings[$name] = include __DIR__ . "$path/$file";
            }
        }
        return $settings;
    }
}

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

require_once __DIR__ . '/../routes/web.php';

return $app;
