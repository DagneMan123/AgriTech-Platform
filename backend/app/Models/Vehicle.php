<?php
// app/Models/Vehicle.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'transporter_id',
        'vehicle_type',
        'vehicle_plate',
        'vehicle_model',
        'vehicle_brand',
        'vehicle_year',
        'capacity_kg',
        'capacity_volume',
        'capacity_unit',
        'color',
        'insurance_expiry',
        'license_expiry',
        'status'
    ];

    protected $casts = [
        'vehicle_year' => 'integer',
        'capacity_kg' => 'decimal:2',
        'capacity_volume' => 'decimal:2',
        'insurance_expiry' => 'date',
        'license_expiry' => 'date'
    ];

    public function transporter()
    {
        return $this->belongsTo(Transporter::class);
    }
    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'available' => 'Available',
            'in_use' => 'In Use',
            'maintenance' => 'Maintenance',
            'inactive' => 'Inactive'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }
}
