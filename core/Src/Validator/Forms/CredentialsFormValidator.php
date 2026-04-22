<?php

namespace Src\Validator\Forms;

use Src\Validator\FormValidator;

class CredentialsFormValidator extends FormValidator
{
    protected function rules(): array
    {
        return [
            'login' => ['required:Логин', 'plainText:Логин', 'max:Логин,120'],
            'password' => ['required:Пароль', 'max:Пароль,255'],
        ];
    }
}
