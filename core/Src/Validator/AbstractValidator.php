<?php

namespace Src\Validator;

abstract class AbstractValidator
{
    protected string $fieldName;
    protected mixed $value;
    protected array $args;
    protected ?string $message;

    public function __construct(string $fieldName, mixed $value, array $args = [], ?string $message = null)
    {
        $this->fieldName = $fieldName;
        $this->value = $value;
        $this->args = $args;
        $this->message = $message;
    }

    abstract public function rule(): bool;

    abstract public function validate(): string;

    protected function label(): string
    {
        return trim((string)($this->args[0] ?? $this->fieldName));
    }

    protected function messageOr(string $default): string
    {
        return $this->message !== null && $this->message !== ''
            ? $this->message
            : $default;
    }
}
