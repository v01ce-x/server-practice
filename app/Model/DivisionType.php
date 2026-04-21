<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class DivisionType extends Model
{
    public $timestamps = false;

    protected $table = 'types_division';

    protected $fillable = [
        'type_name',
    ];
}
