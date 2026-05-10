<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'guest_id',
        'room_id',
        'check_in',
        'check_out',
        'duration_type',
        'total_amount',
        'extended_hours',
        'extended_fee',
        'final_amount',
        'status',
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    public function payment()
    {   
        return $this->hasOne(Payment::class);
    }
}