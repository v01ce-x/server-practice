<?php

namespace Src\Validator;

abstract class FormValidator
{
    private ?Validator $validator = null;

    public function __construct(protected array $fields)
    {
    }

    abstract protected function rules(): array;

    protected function customMessages(): array
    {
        return [];
    }

    public function errors(): array
    {
        return $this->validator()->errors();
    }

    public function messages(): array
    {
        return $this->validator()->messages();
    }

    public function fails(): bool
    {
        return $this->validator()->fails();
    }

    public function passes(): bool
    {
        return $this->validator()->passes();
    }

    protected function validator(): Validator
    {
        return $this->validator ??= new Validator(
            $this->fields,
            $this->rules(),
            $this->customMessages()
        );
    }
}
