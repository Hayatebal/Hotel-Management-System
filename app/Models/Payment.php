<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'reservation_id',
        'amount_paid',
        'balance',
        'payment_method',
        'payment_status',
        'reference_number',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}