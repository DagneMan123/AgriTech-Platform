<?php
// app/Models/DeliveryTracking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'status',
        'location',
        'latitude',
        'longitude',
        'notes',
        'estimated_arrival',
        'distance',
        'duration'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'distance' => 'decimal:2',
        'duration' => 'decimal:2',
        'estimated_arrival' => 'datetime'
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }
}
