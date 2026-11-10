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
        'escrow_status',
        'escrow_held_at',
        'escrow_disbursed_at',
        'payout_reference',
        'payout_account_details',
        'notes',
        'delivered_at',
        'cancelled_at'
    ];

    protected $appends = [
        'has_active_refund_claim',
        'is_refund_settled',
        'pickup_location',
        'origin_location'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'escrow_held_at' => 'datetime',
        'escrow_disbursed_at' => 'datetime',
        'payout_account_details' => 'array',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];

    public function getStatusAttribute($value)
    {
        $val = strtolower($value ?? 'pending');
        if (in_array($val, ['refunded', 'cancelled', 'rejected', 'refund_requested', 'under_review', 'dispute', 'disputed'])) {
            return $val === 'dispute' ? 'disputed' : $val;
        }
        $refund = $this->relationLoaded('refund') ? $this->refund : ($this->exists ? $this->refund()->first() : null);
        if ($refund) {
            $refundStatus = strtolower($refund->status ?? '');
            if (in_array($refundStatus, ['approved', 'completed', 'refunded'])) {
                return 'refunded';
            }
            if (in_array($refundStatus, ['dispute', 'disputed'])) {
                return 'disputed';
            }
            if (in_array($refundStatus, ['under_review', 'in_review'])) {
                return 'under_review';
            }
            if (in_array($refundStatus, ['requested', 'pending'])) {
                return 'refund_requested';
            }
        }
        return $val;
    }

    public function getPaymentStatusAttribute($value)
    {
        if (strtolower($value ?? '') === 'refunded' || strtolower($this->status ?? '') === 'refunded') {
            return 'refunded';
        }
        $refund = $this->relationLoaded('refund') ? $this->refund : ($this->exists ? $this->refund()->first() : null);
        if ($refund && in_array(strtolower($refund->status ?? ''), ['approved', 'completed', 'refunded'])) {
            return 'refunded';
        }
        if (in_array(strtolower($value ?? ''), ['paid', 'completed'])) {
            return 'paid';
        }
        if ($this->relationLoaded('payment') && $this->payment && in_array(strtolower($this->payment->status ?? ''), ['completed', 'paid'])) {
            return 'paid';
        }
        return strtolower($value ?: 'pending');
    }

    public function hasActiveRefundClaim(): bool
    {
        if (in_array(strtolower($this->status ?? ''), ['refunded'])) {
            return false;
        }
        if (in_array(strtolower($this->status ?? ''), ['refund_requested', 'under_review', 'dispute', 'disputed'])) {
            return true;
        }
        $refund = $this->relationLoaded('refund') ? $this->refund : ($this->exists ? $this->refund()->first() : null);
        if ($refund) {
            return in_array(strtolower($refund->status ?? ''), ['requested', 'pending', 'under_review', 'in_review', 'dispute', 'disputed']);
        }
        return false;
    }

    public function getHasActiveRefundClaimAttribute(): bool
    {
        return $this->hasActiveRefundClaim();
    }

    public function isRefundSettled(): bool
    {
        if (in_array(strtolower($this->status ?? ''), ['refunded'])) {
            return true;
        }
        if (strtolower($this->payment_status ?? '') === 'refunded') {
            return true;
        }
        $refund = $this->relationLoaded('refund') ? $this->refund : ($this->exists ? $this->refund()->first() : null);
        if ($refund) {
            return in_array(strtolower($refund->status ?? ''), ['approved', 'completed', 'refunded']);
        }
        return false;
    }

    public function getIsRefundSettledAttribute(): bool
    {
        return $this->isRefundSettled();
    }

    public function getPickupLocationAttribute(): string
    {
        return $this->getRealDataOrigin();
    }

    public function getOriginLocationAttribute(): string
    {
        return $this->getRealDataOrigin();
    }

    public function getRealDataOrigin(): string
    {
        // 1. Delivery assigned pickup/origin address if set
        $delivery = $this->delivery;
        if ($delivery) {
            $loc = trim($delivery->pickup_location ?? $delivery->origin_location ?? $delivery->pickup_address ?? '');
            if (!empty($loc) && !str_contains(strtolower($loc), 'sample') && !str_contains(strtolower($loc), 'farm pickup point')) {
                return $loc;
            }
        }

        // 2. Direct farmer or farmer linked through items
        $farmer = $this->farmer;
        if (!$farmer && $this->items && $this->items->isNotEmpty()) {
            $farmer = $this->items->first()?->product?->farmer;
        }

        // 3. Product origin from order item
        $productOrigin = null;
        if ($this->items && $this->items->isNotEmpty()) {
            $firstProduct = $this->items->first()?->product;
            if ($firstProduct && !empty($firstProduct->origin_location)) {
                $productOrigin = trim($firstProduct->origin_location);
            }
        }

        if ($farmer) {
            $parts = [];
            if (!empty($farmer->farm_name) && !str_contains(strtolower($farmer->farm_name), 'sample')) {
                $parts[] = trim($farmer->farm_name);
            } elseif (!empty($farmer->cooperative_name) && !str_contains(strtolower($farmer->cooperative_name), 'sample')) {
                $parts[] = trim($farmer->cooperative_name);
            }

            if (!empty($farmer->kebele)) {
                $parts[] = 'Kebele ' . trim($farmer->kebele);
            }
            if (!empty($farmer->woreda)) {
                $parts[] = trim($farmer->woreda);
            } elseif ($productOrigin) {
                $parts[] = $productOrigin;
            }
            if (!empty($farmer->zone)) {
                $parts[] = trim($farmer->zone) . (str_contains(strtolower($farmer->zone), 'zone') ? '' : ' Zone');
            }
            if (!empty($farmer->region)) {
                $parts[] = trim($farmer->region);
            }
            $parts[] = 'Ethiopia';

            $locationString = implode(', ', array_unique(array_filter($parts)));
            if (!empty($locationString)) {
                return $locationString;
            }
        }

        if ($productOrigin) {
            return $productOrigin . ', Ethiopia';
        }

        return 'Ethiopia';
    }

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
    public function supplier()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id');
    }
    public function farm()
    {
        return $this->hasOneThrough(Farm::class, Farmer::class, 'id', 'farmer_id', 'farmer_id', 'id');
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function items()
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
    public function refund()
    {
        return $this->hasOne(Refund::class)->latestOfMany();
    }
    public function refunds()
    {
        return $this->hasMany(Refund::class);
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
            'pending' => 'Pending Confirmation / Placed',
            'placed' => 'Pending Confirmation / Placed',
            'confirmed' => 'Confirmed / Preparing',
            'approved' => 'Confirmed / Preparing',
            'processing' => 'Confirmed / Preparing',
            'preparing' => 'Confirmed / Preparing',
            'shipped' => 'In Transit / Shipped',
            'in_transit' => 'In Transit / Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded'
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
            'cancelled' => 'bg-red-100 text-red-800',
            'refunded' => 'bg-rose-100 text-rose-800',
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
