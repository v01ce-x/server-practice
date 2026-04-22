<?php

namespace Src\Validator\Forms;

use Src\Validator\FormValidator;

class RoomFormValidator extends FormValidator
{
    protected function rules(): array
    {
        return [
            'name' => ['required:Название или номер помещения', 'plainText:Название или номер помещения', 'max:Название или номер помещения,120'],
            'type' => ['required:Вид помещения', 'plainText:Вид помещения', 'max:Вид помещения,80'],
        ];
    }
}
