<?php
// app/Models/Transporter.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transporter extends Model
{
    use HasFactory;

    protected $table = 'transporter_profiles';

    protected $fillable = [
        'user_id',
        'company_name',
        'license_number',
        'address',
        'service_areas',
        'vehicle_types',
        'is_verified'
    ];

    protected $casts = [
        'service_areas' => 'array',
        'vehicle_types' => 'array',
        'is_verified' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function getTotalDeliveriesAttribute()
    {
        return $this->deliveries()->count();
    }

    public function getCompletedDeliveriesAttribute()
    {
        return $this->deliveries()->where('status', 'delivered')->count();
    }

    public function getTotalEarningsAttribute()
    {
        return $this->deliveries()->where('status', 'delivered')->sum('delivery_fee') ?? 0;
    }
}
