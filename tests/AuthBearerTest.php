<?php

declare(strict_types=1);

require_once __DIR__ . '/TestBootstrap.php';

use PHPUnit\Framework\TestCase;
use Src\Auth\Auth;
use Src\Auth\IdentityInterface;

final class AuthBearerTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_SERVER = [];
    }

    public function testBearerTokenAuthenticatesUser(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer test-token';

        Auth::init(new FakeIdentityUser());

        $this->assertTrue(Auth::check());
        $this->assertSame(7, Auth::user()?->getId());
    }

    public function testApiAttemptReturnsNewToken(): void
    {
        Auth::init(new FakeIdentityUser());

        $token = Auth::attemptApi([
            'login' => 'api-user',
            'password' => 'secret',
        ]);

        $this->assertSame('issued-token', $token);
        $this->assertSame(7, Auth::user()?->getId());
    }
}

final class FakeIdentityUser implements IdentityInterface
{
    private int $id = 7;

    public function findIdentity(int $id)
    {
        return $id === $this->id ? $this : null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function attemptIdentity(array $credentials)
    {
        if (($credentials['login'] ?? '') !== 'api-user' || ($credentials['password'] ?? '') !== 'secret') {
            return null;
        }

        return $this;
    }

    public function findIdentityByToken(string $token)
    {
        return $token === 'test-token' ? $this : null;
    }

    public function issueApiToken(): string
    {
        return 'issued-token';
    }
}
