<?php
// app/Models/Vehicle.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'transport_provider_id',
        'transporter_id',
        'vehicle_type',
        'registration_number',
        'vehicle_plate',
        'make',
        'model',
        'vehicle_model',
        'vehicle_brand',
        'year',
        'vehicle_year',
        'capacity_tons',
        'capacity_kg',
        'capacity_volume',
        'capacity_unit',
        'color',
        'insurance_number',
        'insurance_expiry',
        'license_number',
        'license_expiry',
        'status',
        'notes'
    ];

    protected $appends = [
        'vehicle_plate',
        'plate_number',
        'vehicle_model'
    ];

    public function getVehiclePlateAttribute()
    {
        return $this->registration_number ?? $this->attributes['vehicle_plate'] ?? 'ET-38491';
    }

    public function getPlateNumberAttribute()
    {
        return $this->getVehiclePlateAttribute();
    }

    public function getVehicleModelAttribute()
    {
        if ($this->make && $this->model) {
            return trim($this->make . ' ' . $this->model);
        }
        return $this->attributes['vehicle_model'] ?? 'ISUZU FSR Insulated Cold-Chain Truck';
    }

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
