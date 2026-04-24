<?php

declare(strict_types=1);

require_once __DIR__ . '/TestBootstrap.php';

use PHPUnit\Framework\TestCase;
use Src\Validator\Forms\CredentialsFormValidator;

final class TestAuthTest extends TestCase
{
    /**
     * @test
     * @dataProvider loginProvider
     */
    public function loginValidationTest(array $formData, array $expectedMessages): void
    {
        $validator = new CredentialsFormValidator($formData);

        $this->assertSame($expectedMessages, $validator->messages());
    }

    public function loginProvider(): array
    {
        return [
            'empty login and password' => [
                [
                    'login' => '',
                    'password' => '',
                ],
                [
                    'Поле «Логин» обязательно для заполнения.',
                    'Поле «Пароль» обязательно для заполнения.',
                ],
            ],
            'login with html' => [
                [
                    'login' => '<admin>',
                    'password' => '123456',
                ],
                [
                    'Поле «Логин» не должно содержать HTML или угловые скобки.',
                ],
            ],
            'valid login' => [
                [
                    'login' => 'admin',
                    'password' => '123456',
                ],
                [],
            ],
        ];
    }
}
