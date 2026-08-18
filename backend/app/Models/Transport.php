<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transporters';

    protected $fillable = [
        'user_id',
        'company_name',
        'license_plate',
        'license_number',
        'insurance_number',
        'vehicle_type',
        'vehicle_capacity',
        'service_areas',
        'price_per_km',
        'contact_person',
        'emergency_contact',
        'total_earnings',
        'completed_deliveries',
        'average_rating',
        'verification_status',
        'is_active',
    ];

    protected $casts = [
        'vehicle_capacity' => 'decimal:2',
        'price_per_km' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'transporter_id');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
}
