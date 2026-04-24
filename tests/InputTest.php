<?php

declare(strict_types=1);

require_once __DIR__ . '/TestBootstrap.php';

use PHPUnit\Framework\TestCase;
use Src\Security\Input;

final class InputTest extends TestCase
{
    /**
     * @test
     * @dataProvider textProvider
     */
    public function textSanitizationTest(mixed $value, int $maxLength, string $expected): void
    {
        $this->assertSame($expected, Input::text($value, $maxLength));
    }

    public static function textProvider(): array
    {
        return [
            'trim whitespace and strip control chars' => [
                'value' => "  admin\x07  ",
                'maxLength' => 255,
                'expected' => 'admin',
            ],
            'truncate multibyte string' => [
                'value' => 'Супердлинный',
                'maxLength' => 5,
                'expected' => 'Супер',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider rawProvider
     */
    public function rawSanitizationTest(mixed $value, int $maxLength, string $expected): void
    {
        $this->assertSame($expected, Input::raw($value, $maxLength));
    }

    public static function rawProvider(): array
    {
        return [
            'preserve outer whitespace' => [
                'value' => "  ab\x07  ",
                'maxLength' => 255,
                'expected' => '  ab  ',
            ],
            'ignore arrays' => [
                'value' => ['abc'],
                'maxLength' => 255,
                'expected' => '',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider numericStringProvider
     */
    public function numericStringTest(mixed $value, string $expected): void
    {
        $this->assertSame($expected, Input::numericString($value));
    }

    public static function numericStringProvider(): array
    {
        return [
            'trim valid digits' => [
                'value' => ' 123 ',
                'expected' => '123',
            ],
            'reject mixed string' => [
                'value' => '12a',
                'expected' => '',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider enumProvider
     */
    public function enumTest(mixed $value, array $allowed, string $default, string $expected): void
    {
        $this->assertSame($expected, Input::enum($value, $allowed, $default));
    }

    public static function enumProvider(): array
    {
        return [
            'return allowed trimmed value' => [
                'value' => ' published ',
                'allowed' => ['draft', 'published'],
                'default' => 'draft',
                'expected' => 'published',
            ],
            'fallback for invalid value' => [
                'value' => 'archived',
                'allowed' => ['draft', 'published'],
                'default' => 'draft',
                'expected' => 'draft',
            ],
        ];
    }

    /** @test */
    public function escapeLikeTest(): void
    {
        $this->assertSame(
            '50\\%\\_done\\\\test',
            Input::escapeLike('50%_done\\test')
        );
    }
}
