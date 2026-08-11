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
        'status',
        'pickup_location',
        'delivery_location',
        'pickup_address',
        'delivery_address',
        'pickup_time',
        'delivery_time',
        'estimated_delivery_time',
        'delivery_fee',
        'tracking_info',
        'driver_name',
        'driver_phone',
        'notes'
    ];

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
        return $this->belongsTo(User::class, 'transporter_id');
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
