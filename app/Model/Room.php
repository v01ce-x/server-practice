<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'type_id',
    ];

    public function typeRelation()
    {
        return $this->belongsTo(RoomType::class, 'type_id');
    }

    public function phones()
    {
        return $this->hasMany(Phone::class, 'room_id');
    }

    public function getTypeAttribute(): string
    {
        return (string)($this->typeRelation?->type_name ?? '');
    }

    public function getFullLabelAttribute(): string
    {
        return $this->type ?: 'Тип помещения не задан';
    }
}
