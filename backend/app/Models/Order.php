<?php
// app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'buyer_id',
        'farmer_id',
        'subtotal',
        'delivery_fee',
        'tax',
        'discount',
        'grand_total',
        'status',
        'delivery_address',
        'delivery_region',
        'delivery_zone',
        'delivery_woreda',
        'delivery_phone',
        'delivery_instructions',
        'payment_method',
        'payment_status',
        'payment_reference',
        'notes',
        'delivered_at',
        'cancelled_at'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            $order->order_number = 'ORD-' . date('Ymd') . '-' . Str::random(8);
        });
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'processing' => 'bg-purple-100 text-purple-800',
            'shipped' => 'bg-indigo-100 text-indigo-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800'
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'approved']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }
    public function scopeByBuyer($query, $buyerId)
    {
        return $query->where('buyer_id', $buyerId);
    }
    public function scopeByFarmer($query, $farmerId)
    {
        return $query->where('farmer_id', $farmerId);
    }
}
