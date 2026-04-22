<?php

namespace Src;

use DateTimeImmutable;

class FormValidator
{
    private array $errors = [];

    public function required(string $label, mixed $value): self
    {
        if (trim((string)$value) === '') {
            $this->errors[] = "Поле «{$label}» обязательно для заполнения.";
        }

        return $this;
    }

    public function russianDate(string $label, mixed $value): self
    {
        $value = trim((string)$value);

        if ($value === '') {
            $this->errors[] = "Поле «{$label}» обязательно для заполнения.";
            return $this;
        }

        if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value)) {
            $this->errors[] = "Поле «{$label}» должно быть в формате ДД.ММ.ГГГГ.";
            return $this;
        }

        $date = DateTimeImmutable::createFromFormat('!d.m.Y', $value);
        $errors = DateTimeImmutable::getLastErrors();
        $hasErrors = $errors !== false
            && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0);

        if (!$date || $hasErrors) {
            $this->errors[] = "Поле «{$label}» содержит некорректную дату.";
        }

        return $this;
    }

    public function errors(): array
    {
        return array_values(array_unique($this->errors));
    }

    public function passes(): bool
    {
        return $this->errors() === [];
    }
}
