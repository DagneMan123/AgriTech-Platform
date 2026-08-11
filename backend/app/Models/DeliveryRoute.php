<?php
// app/Models/DeliveryRoute.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'start_location',
        'end_location',
        'distance',
        'estimated_time',
        'waypoints'
    ];

    protected $casts = [
        'distance' => 'decimal:2',
        'estimated_time' => 'decimal:2',
        'waypoints' => 'array'
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }
}
