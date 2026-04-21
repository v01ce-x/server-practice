<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
use Src\Auth\IdentityInterface;

class User extends Model implements IdentityInterface
{
    public const ROLE_ADMINISTRATOR = 'administrator';
    public const ROLE_SYSTEM_ADMIN = 'system_admin';
    public const STATUS_ACTIVE = 'active';

    public $timestamps = false;

    protected $table = 'admins';

    protected $fillable = [
        'login',
        'password',
        'role_id',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $user) {
            if ($user->isDirty('password') && !self::isPasswordHash($user->password)) {
                $user->password = password_hash($user->password, PASSWORD_DEFAULT);
            }
        });
    }

    public static function isPasswordHash(string $value): bool
    {
        return (password_get_info($value)['algo'] ?? 0) !== 0;
    }

    public function findIdentity(int $id)
    {
        return self::query()->find($id);
    }

    public function getId(): int
    {
        return (int)$this->id;
    }

    public function attemptIdentity(array $credentials)
    {
        if (!isset($credentials['login'], $credentials['password'])) {
            return null;
        }

        $query = self::query()
            ->with('roleRelation')
            ->where('login', $credentials['login']);

        if (!empty($credentials['auth_role'])) {
            $query->whereHas('roleRelation', function ($roleQuery) use ($credentials) {
                $roleQuery->where('role', $credentials['auth_role']);
            });
        }

        /** @var self|null $user */
        $user = $query->first();
        if (!$user) {
            return null;
        }

        if (self::isPasswordHash((string)$user->password)) {
            return password_verify($credentials['password'], $user->password) ? $user : null;
        }

        if ((string)$user->password !== (string)$credentials['password']) {
            return null;
        }

        $user->password = (string)$credentials['password'];
        $user->save();

        return $user;
    }

    public function roleRelation()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getRoleAttribute(): string
    {
        return (string)($this->roleRelation?->role ?? '');
    }

    public function getFullNameAttribute(): string
    {
        return (string)$this->login;
    }

    public function getStatusAttribute(): string
    {
        return self::STATUS_ACTIVE;
    }

    public function isAdministrator(): bool
    {
        return $this->role === self::ROLE_ADMINISTRATOR;
    }

    public function isSystemAdmin(): bool
    {
        return $this->role === self::ROLE_SYSTEM_ADMIN;
    }

    public function getRoleLabel(): string
    {
        return $this->isAdministrator() ? 'Администратор системы' : 'Системный администратор';
    }

    public function getStatusLabel(): string
    {
        return 'Активен';
    }

    public function getShortName(): string
    {
        return (string)$this->login;
    }
}
