<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class PhoneAssignment extends Model
{
    protected $fillable = [
        'subscriber_id',
        'phone_id',
        'label',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'bool',
    ];

    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function phone()
    {
        return $this->belongsTo(Phone::class);
    }
}
