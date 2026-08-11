<?php
// app/Models/SupplierOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SupplierOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'supplier_id',
        'farmer_id',
        'subtotal',
        'delivery_fee',
        'tax',
        'grand_total',
        'status',
        'delivery_address',
        'delivery_phone',
        'payment_method',
        'payment_status',
        'notes',
        'delivered_at'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'delivered_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            $order->order_number = 'SORD-' . date('Ymd') . '-' . Str::random(8);
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }
    public function items()
    {
        return $this->hasMany(SupplierOrderItem::class);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }
}
