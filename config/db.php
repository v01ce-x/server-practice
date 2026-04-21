<?php

return [
    'driver' => getenv('DB_DRIVER') ?: 'mysql',
    'host' => getenv('DB_HOST') ?: 'db',
    'port' => getenv('DB_PORT') ?: 3306,
    'database' => getenv('DB_DATABASE') ?: 'db',
    'username' => getenv('APP_DB_USERNAME') ?: (getenv('DB_USERNAME') ?: 'user'),
    'password' => getenv('APP_DB_PASSWORD') ?: (getenv('DB_PASSWORD') ?: 'password'),
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
    'strict' => false,
];
