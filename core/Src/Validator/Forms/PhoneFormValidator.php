<?php

namespace Src\Validator\Forms;

use Src\Validator\FormValidator;

class PhoneFormValidator extends FormValidator
{
    public function __construct(array $fields, private bool $withSubscriber = false)
    {
        parent::__construct($fields);
    }

    protected function rules(): array
    {
        $rules = [
            'number' => ['required:Номер телефона', 'phone:Номер телефона', 'max:Номер телефона,32'],
            'room_id' => ['required:Помещение', 'integer:Помещение'],
        ];

        if ($this->withSubscriber) {
            $rules['subscriber_id'] = ['required:Абонент', 'integer:Абонент'];
        }

        return $rules;
    }
}
