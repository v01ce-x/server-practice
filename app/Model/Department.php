<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    public $timestamps = false;

    protected $table = 'divisions';

    protected $fillable = [
        'name',
        'type_id',
        'admin_id',
    ];

    public function subscribers()
    {
        return $this->hasMany(Subscriber::class, 'division_id');
    }

    public function typeRelation()
    {
        return $this->belongsTo(DivisionType::class, 'type_id');
    }

    public function getTypeAttribute(): string
    {
        return (string)($this->typeRelation?->type_name ?? '');
    }
}
