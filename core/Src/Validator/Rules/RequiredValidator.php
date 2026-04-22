<?php

namespace Src\Validator\Rules;

use Src\Validator\AbstractValidator;

class RequiredValidator extends AbstractValidator
{
    public function rule(): bool
    {
        return trim((string)$this->value) !== '';
    }

    public function validate(): string
    {
        return $this->messageOr("Поле «{$this->label()}» обязательно для заполнения.");
    }
}
