<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'last_name',
        'first_name',
        'patronymic',
        'birthdate',
        'division_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'division_id');
    }

    public function phone()
    {
        return $this->hasOne(Phone::class, 'subscriber_id');
    }

    public function getMiddleNameAttribute(): string
    {
        return (string)$this->patronymic;
    }

    public function setMiddleNameAttribute(?string $value): void
    {
        $this->attributes['patronymic'] = $value;
    }

    public function getBirthDateAttribute(): string
    {
        return (string)$this->birthdate;
    }

    public function setBirthDateAttribute(?string $value): void
    {
        $this->attributes['birthdate'] = $value;
    }

    public function getDepartmentIdAttribute(): int
    {
        return (int)$this->division_id;
    }

    public function setDepartmentIdAttribute(int|string|null $value): void
    {
        $this->attributes['division_id'] = $value;
    }

    public function getStatusAttribute(): string
    {
        return 'active';
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->last_name,
            $this->first_name,
            $this->patronymic,
        ])));
    }

    public function getInitialsAttribute(): string
    {
        $parts = array_filter([$this->last_name, $this->first_name, $this->patronymic]);
        $letters = [];
        foreach ($parts as $part) {
            $letters[] = mb_substr($part, 0, 1);
        }

        return implode('', array_slice($letters, 0, 2));
    }

    public function getBirthDateFormattedAttribute(): string
    {
        if (!$this->birthdate) {
            return '';
        }

        $chunks = explode('-', (string)$this->birthdate);
        if (count($chunks) !== 3) {
            return (string)$this->birthdate;
        }

        return sprintf('%s.%s.%s', $chunks[2], $chunks[1], $chunks[0]);
    }
}
