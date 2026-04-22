<?php

namespace Src\Validator\Rules;

use Src\Validator\AbstractValidator;

class PhoneValidator extends AbstractValidator
{
    public function rule(): bool
    {
        $value = trim((string)$this->value);
        if ($value === '') {
            return true;
        }

        return preg_match('/^\+?[0-9][0-9\s\-()]{2,31}$/', $value) === 1;
    }

    public function validate(): string
    {
        $label = $this->label();

        return $this->messageOr("Поле «{$label}» должно содержать корректный номер телефона.");
    }
}
