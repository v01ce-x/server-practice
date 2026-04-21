<?php
declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '1');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

function renderBootstrapError(\Throwable $exception): void
{
    $title = 'Не удалось запустить приложение';
    $message = 'Во время инициализации проекта возникла ошибка.';
    $details = [];
    $rawMessage = trim($exception->getMessage());

    if (mb_stripos($rawMessage, 'PDO-драйвера') !== false || mb_stripos($rawMessage, 'could not find driver') !== false) {
        $message = 'PHP запущен без нужного драйвера базы данных.';
        $details[] = 'Этот проект настроен под Docker-окружение с MariaDB.';
        $details[] = 'Запусти `docker compose up -d` в корне проекта или включи `pdo_mysql` в локальном PHP.';
    } elseif (mb_stripos($rawMessage, 'Connection refused') !== false || mb_stripos($rawMessage, 'php_network_getaddresses') !== false) {
        $message = 'Приложение не смогло подключиться к базе данных.';
        $details[] = 'Проверь, что контейнер `db` поднят и доступен.';
        $details[] = 'Если запускаешь проект не в Docker, задай корректные `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.';
    } elseif ($rawMessage !== '') {
        $details[] = $rawMessage;
    }

    error_log($exception::class . ': ' . $rawMessage);

    http_response_code(500);
    ?>
    <!doctype html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
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
                width: min(720px, calc(100vw - 32px));
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

            ul {
                margin: 18px 0 0;
                padding-left: 20px;
            }

            li + li {
                margin-top: 8px;
            }

            code {
                padding: 2px 6px;
                border-radius: 6px;
                background: #eef3f7;
                color: #177d74;
            }
        </style>
    </head>
    <body>
    <main class="error-card">
        <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php if ($details !== []): ?>
            <ul>
                <?php foreach ($details as $detail): ?>
                    <li><?= htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
    </body>
    </html>
    <?php
}

try {
    $app = require_once __DIR__ . '/../core/bootstrap.php';
    $app->run();
} catch (\Throwable $exception) {
    renderBootstrapError($exception);
}
