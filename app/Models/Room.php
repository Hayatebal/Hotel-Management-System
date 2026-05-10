<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'room_number',
        'room_type',
        'price_3hrs',
        'price_6hrs',
        'price_8hrs',
        'price_12hrs',
        'price_24hrs',
        'status',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}