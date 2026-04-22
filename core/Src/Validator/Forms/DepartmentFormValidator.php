<?php

namespace Src\Validator\Forms;

use Src\Validator\FormValidator;

class DepartmentFormValidator extends FormValidator
{
    protected function rules(): array
    {
        return [
            'name' => ['required:Название подразделения', 'plainText:Название подразделения', 'max:Название подразделения,120'],
            'type' => ['required:Вид подразделения', 'plainText:Вид подразделения', 'max:Вид подразделения,80'],
        ];
    }
}
