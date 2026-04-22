<?php

namespace Src\Validator\Rules;

use Src\Validator\AbstractValidator;

class IntegerValidator extends AbstractValidator
{
    public function rule(): bool
    {
        $value = trim((string)$this->value);
        if ($value === '') {
            return true;
        }

        return ctype_digit($value);
    }

    public function validate(): string
    {
        $label = $this->label();

        return $this->messageOr("Поле «{$label}» должно содержать корректный идентификатор.");
    }
}
