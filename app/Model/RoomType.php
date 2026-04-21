<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    public $timestamps = false;

    protected $table = 'types_room';

    protected $fillable = [
        'type_name',
    ];
}
