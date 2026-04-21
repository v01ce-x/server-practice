<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = false;

    protected $table = 'roles';

    protected $fillable = [
        'role',
    ];

    public static function idFor(string $role): int
    {
        return (int)self::query()->firstOrCreate(['role' => $role])->id;
    }
}
