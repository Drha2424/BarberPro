<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory, HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'booking_code',
        'customer_name',
        'customer_phone',
        'service_id',
        'booking_date',
        'booking_time',
        'notes',
        'payment_method',
        'total_price',
        'status',
        'payment_status',
    ];

    protected $casts = [
        'booking_date' => 'date:Y-m-d',
        'total_price' => 'float',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Reservasi yang masih memakai slot (selain yang dibatalkan)
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status')->orWhere('status', '!=', 'Cancelled');
        });
    }
}