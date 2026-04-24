<?php

declare(strict_types=1);

require_once __DIR__ . '/TestBootstrap.php';

use PHPUnit\Framework\TestCase;
use Src\Settings;
use Src\View;

final class ViewTest extends TestCase
{
    protected function setUp(): void
    {
        global $app;

        $app = new TestBootstrap(new Settings([
            'app' => require __DIR__ . '/../config/app.php',
            'path' => require __DIR__ . '/../config/path.php',
        ]));
    }

    /** @test */
    public function renderShowsMissingViewPath(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Error render: view `' . dirname(__DIR__) . '/views/site/missing.php` not found'
        );

        (new View())->render('site.missing');
    }

    /** @test */
    public function renderShowsMissingLayoutPath(): void
    {
        $view = new View();
        $reflection = new ReflectionClass($view);
        $layout = $reflection->getProperty('layout');
        $layout->setAccessible(true);
        $layout->setValue($view, '/layouts/missing.php');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Error render: layout `' . dirname(__DIR__) . '/views/layouts/missing.php` not found'
        );

        $view->render('site.login');
    }
}
