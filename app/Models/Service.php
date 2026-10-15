<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_minutes',
        'icon',
        'badge',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];
}