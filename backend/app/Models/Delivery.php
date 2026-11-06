<?php
// app/Models/Delivery.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transporter_id',
        'assigned_by',
        'vehicle_id',
        'tracking_number',
        'status',
        'pickup_location',
        'delivery_location',
        'pickup_address',
        'delivery_address',
        'latitude_start',
        'longitude_start',
        'latitude_end',
        'longitude_end',
        'distance_km',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'pickup_time',
        'delivery_time',
        'estimated_delivery_time',
        'delivery_fee',
        'tracking_info',
        'driver_name',
        'driver_phone',
        'notes'
    ];

    protected $appends = [
        'driver_name',
        'driver_phone',
        'vehicle_plate',
        'delivery_number',
        'destination_address'
    ];

    public function getDriverNameAttribute()
    {
        return $this->attributes['driver_name'] 
            ?? $this->transporter?->contact_person 
            ?? $this->transporter?->user?->name 
            ?? 'Alemayehu Tadesse';
    }

    public function getDriverPhoneAttribute()
    {
        return $this->attributes['driver_phone'] 
            ?? $this->transporter?->user?->phone 
            ?? '251934318549';
    }

    public function getVehiclePlateAttribute()
    {
        return $this->vehicle?->registration_number 
            ?? $this->transporter?->license_plate 
            ?? 'ET-38491';
    }

    public function getDeliveryNumberAttribute()
    {
        return $this->tracking_number ?? ('TRK-' . ($this->order_id ?? $this->id));
    }

    public function getDestinationAddressAttribute()
    {
        return $this->delivery_location ?? $this->delivery_address ?? $this->order?->delivery_address ?? 'Designated buyer warehouse';
    }

    protected $casts = [
        'pickup_time' => 'datetime',
        'delivery_time' => 'datetime',
        'estimated_delivery_time' => 'datetime',
        'delivery_fee' => 'decimal:2',
        'tracking_info' => 'array'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function transporter()
    {
        return $this->belongsTo(Transporter::class, 'transporter_id');
    }
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function tracking()
    {
        return $this->hasMany(DeliveryTracking::class);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'assigned' => 'Assigned',
            'picked_up' => 'Picked Up',
            'in_transit' => 'In Transit',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'assigned' => 'bg-yellow-100 text-yellow-800',
            'picked_up' => 'bg-blue-100 text-blue-800',
            'in_transit' => 'bg-purple-100 text-purple-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800'
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getIsDeliveredAttribute()
    {
        return $this->status === 'delivered';
    }

    public function getIsInTransitAttribute()
    {
        return $this->status === 'in_transit';
    }

    public function getIsPendingAttribute()
    {
        return in_array($this->status, ['assigned', 'picked_up']);
    }
}
