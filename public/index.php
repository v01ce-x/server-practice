<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/../core/bootstrap.php';
    $app->run();
} catch (\Throwable $exception) {
    echo '<pre>';
    print_r($exception);
    echo '</pre>';
}
