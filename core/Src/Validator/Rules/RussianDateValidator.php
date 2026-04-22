<?php

namespace Src\Validator\Rules;

use DateTimeImmutable;
use Src\Validator\AbstractValidator;

class RussianDateValidator extends AbstractValidator
{
    private string $failureType = '';

    public function rule(): bool
    {
        $value = trim((string)$this->value);

        if ($value === '') {
            $this->failureType = 'required';
            return false;
        }

        if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value)) {
            $this->failureType = 'format';
            return false;
        }

        $date = DateTimeImmutable::createFromFormat('!d.m.Y', $value);
        $errors = DateTimeImmutable::getLastErrors();
        $hasErrors = $errors !== false
            && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0);

        if (!$date || $hasErrors) {
            $this->failureType = 'invalid';
            return false;
        }

        $this->failureType = '';
        return true;
    }

    public function validate(): string
    {
        $label = $this->label();

        return match ($this->failureType) {
            'required' => $this->messageOr("Поле «{$label}» обязательно для заполнения."),
            'format' => $this->messageOr("Поле «{$label}» должно быть в формате ДД.ММ.ГГГГ."),
            default => $this->messageOr("Поле «{$label}» содержит некорректную дату."),
        };
    }
}
