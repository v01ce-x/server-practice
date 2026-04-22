<?php

namespace Src\Validator;

class Validator
{
    private array $validators = [];
    private array $errors = [];
    private array $fields = [];
    private array $rules = [];
    private array $messages = [];

    public function __construct(array $fields, array $rules, array $messages = [])
    {
        $this->validators = app()->settings->app['validators'] ?? [];
        $this->fields = $fields;
        $this->rules = $rules;
        $this->messages = $messages;
        $this->validateFields();
    }

    private function validateFields(): void
    {
        foreach ($this->rules as $fieldName => $fieldValidators) {
            $this->validateField($fieldName, $fieldValidators);
        }
    }

    private function validateField(string $fieldName, array $fieldValidators): void
    {
        foreach ($fieldValidators as $validatorDefinition) {
            [$validatorName, $args] = $this->parseValidator($validatorDefinition);
            $validatorClass = $this->validators[$validatorName] ?? null;

            if ($validatorClass === null || !class_exists($validatorClass)) {
                continue;
            }

            $validator = new $validatorClass(
                $fieldName,
                $this->fields[$fieldName] ?? null,
                $args,
                $this->messageFor($fieldName, $validatorName)
            );

            if (!$validator->rule()) {
                $this->errors[$fieldName][] = $validator->validate();
            }
        }

        if (isset($this->errors[$fieldName])) {
            $this->errors[$fieldName] = array_values(array_unique($this->errors[$fieldName]));
        }
    }

    private function parseValidator(string $validatorDefinition): array
    {
        $parts = explode(':', $validatorDefinition, 2);
        $validatorName = trim($parts[0]);
        $args = isset($parts[1]) && trim($parts[1]) !== ''
            ? array_map('trim', explode(',', $parts[1]))
            : [];

        return [$validatorName, $args];
    }

    private function messageFor(string $fieldName, string $validatorName): ?string
    {
        $fieldMessages = $this->messages[$fieldName] ?? null;
        if (is_array($fieldMessages) && isset($fieldMessages[$validatorName])) {
            return (string)$fieldMessages[$validatorName];
        }

        $validatorMessage = $this->messages[$validatorName] ?? null;

        return is_string($validatorMessage) ? $validatorMessage : null;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function messages(): array
    {
        $messages = [];

        foreach ($this->errors as $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $messages[] = $error;
            }
        }

        return array_values(array_unique($messages));
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return !$this->fails();
    }
}
