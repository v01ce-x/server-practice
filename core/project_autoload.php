<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    static $prefixes = [
        'Src\\' => __DIR__ . '/Src/',
        'Controller\\' => dirname(__DIR__) . '/app/Controller/',
        'Model\\' => dirname(__DIR__) . '/app/Model/',
        'Middlewares\\' => dirname(__DIR__) . '/app/Middlewares/',
        'Providers\\' => dirname(__DIR__) . '/app/Providers/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require_once $file;
        }

        return;
    }
});
