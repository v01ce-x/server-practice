<?php
const DIR_CONFIG = '/../config';

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

$app = new Src\Application(new Src\Settings(getConfigs()));

function app() {
    global $app;
    return $app;
}

require_once __DIR__ . '/../routes/web.php';

return $app;
