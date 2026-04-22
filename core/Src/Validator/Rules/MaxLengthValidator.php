<?php

namespace Src\Validator\Rules;

use Src\Validator\AbstractValidator;

class MaxLengthValidator extends AbstractValidator
{
    public function rule(): bool
    {
        $value = (string)$this->value;
        $maxLength = (int)($this->args[1] ?? 0);

        if ($value === '' || $maxLength <= 0) {
            return true;
        }

        return mb_strlen($value) <= $maxLength;
    }

    public function validate(): string
    {
        $label = $this->label();
        $maxLength = (int)($this->args[1] ?? 0);

        return $this->messageOr("Поле «{$label}» должно быть не длиннее {$maxLength} символов.");
    }
}
