<?php

declare(strict_types=1);

require_once __DIR__ . '/TestBootstrap.php';

use PHPUnit\Framework\TestCase;
use Src\Validator\Forms\PhoneFormValidator;

final class PhoneFormValidatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider phoneProvider
     */
    public function phoneValidationTest(array $formData, bool $withSubscriber, array $expectedMessages): void
    {
        $validator = new PhoneFormValidator($formData, $withSubscriber);

        $this->assertSame($expectedMessages, $validator->messages());
    }

    public static function phoneProvider(): array
    {
        return [
            'empty phone form' => [
                'formData' => [
                    'number' => '',
                    'room_id' => '',
                ],
                'withSubscriber' => false,
                'expectedMessages' => [
                    'Поле «Номер телефона» обязательно для заполнения.',
                    'Поле «Помещение» обязательно для заполнения.',
                ],
            ],
            'invalid phone and subscriber id' => [
                'formData' => [
                    'number' => '12a',
                    'room_id' => '5',
                    'subscriber_id' => 'abc',
                ],
                'withSubscriber' => true,
                'expectedMessages' => [
                    'Поле «Номер телефона» должно содержать корректный номер телефона.',
                    'Поле «Абонент» должно содержать корректный идентификатор.',
                ],
            ],
            'valid phone with subscriber' => [
                'formData' => [
                    'number' => '+7 (3822) 12-34-56',
                    'room_id' => '5',
                    'subscriber_id' => '12',
                ],
                'withSubscriber' => true,
                'expectedMessages' => [],
            ],
        ];
    }
}
