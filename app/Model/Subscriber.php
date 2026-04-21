<?php

namespace Model;

use DateTimeImmutable;
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

    public function getBirthdateAttribute($value): ?string
    {
        return self::normalizeBirthdateValue($value);
    }

    public function setBirthdateAttribute($value): void
    {
        $this->attributes['birthdate'] = self::normalizeBirthdateValue($value);
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
        $birthdate = $this->birthdate;
        if (!$birthdate) {
            return 'Не указана';
        }

        $chunks = explode('-', $birthdate);
        if (count($chunks) !== 3) {
            return $birthdate;
        }

        return sprintf('%s.%s.%s', $chunks[2], $chunks[1], $chunks[0]);
    }

    private static function normalizeBirthdateValue($value): ?string
    {
        $value = trim((string)$value);
        if ($value === '' || $value === '0000-00-00' || $value === '00.00.0000') {
            return null;
        }

        $formats = [
            '!Y-m-d',
            '!d.m.Y',
            '!d-m-Y',
            '!d/m/Y',
            '!Y/m/d',
        ];

        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            $errors = DateTimeImmutable::getLastErrors();

            if ($date && ($errors === false || (($errors['warning_count'] ?? 0) === 0 && ($errors['error_count'] ?? 0) === 0))) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }
}
