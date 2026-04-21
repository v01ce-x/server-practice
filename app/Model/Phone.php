<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'phone_number',
        'room_id',
        'subscriber_id',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class, 'subscriber_id');
    }

    public function getNumberAttribute(): string
    {
        return (string)$this->phone_number;
    }

    public function setNumberAttribute(?string $value): void
    {
        $this->attributes['phone_number'] = $value;
    }

    public function getKindAttribute(): string
    {
        return 'внутренний';
    }

    public function isAssigned(): bool
    {
        return $this->subscriber_id !== null;
    }
}
