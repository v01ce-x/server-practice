<?php

namespace Src\Validator\Rules;

use Src\Validator\AbstractValidator;

class PlainTextValidator extends AbstractValidator
{
    private string $failureType = '';

    public function rule(): bool
    {
        $value = (string)$this->value;
        if ($value === '') {
            $this->failureType = '';
            return true;
        }

        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value) === 1) {
            $this->failureType = 'control';
            return false;
        }

        if (str_contains($value, '<') || str_contains($value, '>')) {
            $this->failureType = 'html';
            return false;
        }

        $this->failureType = '';
        return true;
    }

    public function validate(): string
    {
        $label = $this->label();

        return match ($this->failureType) {
            'control' => $this->messageOr("Поле «{$label}» содержит недопустимые управляющие символы."),
            default => $this->messageOr("Поле «{$label}» не должно содержать HTML или угловые скобки."),
        };
    }
}
