<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Src\Settings;

final class TestBootstrap
{
    public function __construct(public Settings $settings)
    {
    }
}

$testSettings = new Settings([
    'app' => require __DIR__ . '/../config/app.php',
]);

$app = new TestBootstrap($testSettings);

if (!function_exists('app')) {
    function app()
    {
        global $app;

        return $app;
    }
}
