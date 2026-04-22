<?php

namespace Src\Validator\Forms;

use Src\Validator\FormValidator;

class SubscriberFormValidator extends FormValidator
{
    protected function rules(): array
    {
        return [
            'last_name' => ['required:Фамилия', 'plainText:Фамилия', 'max:Фамилия,80'],
            'first_name' => ['required:Имя', 'plainText:Имя', 'max:Имя,80'],
            'middle_name' => ['plainText:Отчество', 'max:Отчество,80'],
            'birth_date' => ['russianDate:Дата рождения'],
            'department_id' => ['required:Подразделение', 'integer:Подразделение'],
        ];
    }
}
