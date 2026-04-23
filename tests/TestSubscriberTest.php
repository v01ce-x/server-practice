<?php

declare(strict_types=1);

require_once __DIR__ . '/TestBootstrap.php';

use PHPUnit\Framework\TestCase;
use Src\Validator\Forms\SubscriberFormValidator;

final class TestSubscriberTest extends TestCase
{
    /**
     * @test
     * @dataProvider subscriberProvider
     */
    public function subscriberValidationTest(array $formData, array $expectedMessages): void
    {
        $validator = new SubscriberFormValidator($formData);

        $this->assertSame($expectedMessages, $validator->messages());
    }

    public function subscriberProvider(): array
    {
        return [
            'empty first name' => [
                [
                    'last_name' => 'Иванов',
                    'first_name' => '',
                    'middle_name' => '',
                    'birth_date' => '15.09.1995',
                    'department_id' => '1',
                ],
                [
                    'Поле «Имя» обязательно для заполнения.',
                ],
            ],
            'wrong department id' => [
                [
                    'last_name' => 'Иванов',
                    'first_name' => 'Иван',
                    'middle_name' => '',
                    'birth_date' => '15.09.1995',
                    'department_id' => 'abc',
                ],
                [
                    'Поле «Подразделение» должно содержать корректный идентификатор.',
                ],
            ],
            'valid subscriber' => [
                [
                    'last_name' => 'Иванов',
                    'first_name' => 'Иван',
                    'middle_name' => '',
                    'birth_date' => '15.09.1995',
                    'department_id' => '1',
                ],
                [],
            ],
        ];
    }
}
