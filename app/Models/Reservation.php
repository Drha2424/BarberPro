<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'booking_code',
        'customer_name',
        'customer_phone',
        'service_id',
        'booking_date',
        'booking_time',
        'total_price',
        'status',
        'payment_status',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}