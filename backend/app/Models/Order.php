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
        'origin_location',
        'pickup_location',
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
        // Cancellation is the definitive termination of order lifecycle: always preserve 'cancelled'
        if ($val === 'cancelled' || !empty($this->cancelled_at) || str_contains(strtolower($this->notes ?? ''), 'cancellation')) {
            return 'cancelled';
        }
        $refund = $this->relationLoaded('refund') ? $this->refund : ($this->exists ? $this->refund()->first() : null);
        if ($refund && str_contains(strtolower($refund->reason ?? ''), 'cancellation')) {
            return 'cancelled';
        }
        if (in_array($val, ['refunded', 'cancelled', 'rejected', 'refund_requested', 'under_review', 'dispute', 'disputed'])) {
            return $val === 'dispute' ? 'disputed' : $val;
        }
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
        $val = strtolower($value ?? '');
        if ($val === 'refunded' || strtolower($this->escrow_status ?? '') === 'refunded') {
            return 'refunded';
        }
        $rawStatus = strtolower($this->getRawOriginal('status') ?? '');
        if ($rawStatus === 'refunded') {
            return 'refunded';
        }
        $refund = $this->relationLoaded('refund') ? $this->refund : ($this->exists ? $this->refund()->first() : null);
        if ($refund && in_array(strtolower($refund->status ?? ''), ['approved', 'completed', 'refunded'])) {
            return 'refunded';
        }
        if (in_array($val, ['paid', 'completed'])) {
            return 'paid';
        }
        if ($this->relationLoaded('payment') && $this->payment && in_array(strtolower($this->payment->status ?? ''), ['completed', 'paid', 'refunded'])) {
            return strtolower($this->payment->status) === 'refunded' ? 'refunded' : 'paid';
        }
        return strtolower($value ?: 'pending');
    }

    public function hasActiveRefundClaim(): bool
    {
        $currentStatus = strtolower($this->status ?? '');
        if (in_array($currentStatus, ['refunded', 'cancelled']) || !empty($this->cancelled_at)) {
            return false;
        }
        if (in_array($currentStatus, ['refund_requested', 'under_review', 'dispute', 'disputed'])) {
            return true;
        }
        $refund = $this->relationLoaded('refund') ? $this->refund : ($this->exists ? $this->refund()->first() : null);
        if ($refund) {
            if (str_contains(strtolower($refund->reason ?? ''), 'cancellation')) {
                return false;
            }
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

    public function getOriginLocationAttribute(): string
    {
        if ($this->relationLoaded('items') && $this->items && $this->items->count() > 1) {
            return $this->getRealDataOrigin();
        }

        $raw = $this->attributes['origin_location'] ?? null;
        if (!empty($raw) && !str_contains(strtolower($raw), 'sample') && !str_contains(strtolower($raw), 'farm pickup point')) {
            return trim($raw);
        }
        return $this->getRealDataOrigin();
    }

    public function getPickupLocationAttribute(): string
    {
        $raw = $this->attributes['pickup_location'] ?? null;
        if (!empty($raw) && !str_contains(strtolower($raw), 'sample') && !str_contains(strtolower($raw), 'farm pickup point')) {
            return trim($raw);
        }
        return $this->getOriginLocationAttribute();
    }

    public function getRealDataOrigin(): string
    {
        $origins = [];

        // 1. Collect distinct origins from all order items
        if ($this->items && $this->items->isNotEmpty()) {
            foreach ($this->items as $it) {
                $itemOrigin = trim($it->origin_location ?? $it->product?->origin_location ?? '');
                if (!empty($itemOrigin) && !str_contains(strtolower($itemOrigin), 'sample') && !str_contains(strtolower($itemOrigin), 'farm pickup point')) {
                    $cleaned = str_contains($itemOrigin, ',') ? trim(explode(',', $itemOrigin)[0]) : $itemOrigin;
                    if (!empty($cleaned) && !in_array($cleaned, $origins)) {
                        $origins[] = $cleaned;
                    }
                }
            }
        }

        // 2. Direct saved origin on order attributes if items didn't have any
        if (empty($origins)) {
            $rawOrderOrigin = trim($this->attributes['origin_location'] ?? $this->attributes['pickup_location'] ?? '');
            if (!empty($rawOrderOrigin) && !str_contains(strtolower($rawOrderOrigin), 'sample') && !str_contains(strtolower($rawOrderOrigin), 'farm pickup point')) {
                $parts = array_map('trim', explode(',', $rawOrderOrigin));
                foreach ($parts as $p) {
                    if (!empty($p) && !in_array($p, $origins)) {
                        $origins[] = $p;
                    }
                }
            }
        }

        // 3. Delivery assigned pickup/origin address if set
        if (empty($origins)) {
            $delivery = $this->delivery;
            if ($delivery) {
                $loc = trim($delivery->pickup_location ?? $delivery->origin_location ?? $delivery->pickup_address ?? '');
                if (!empty($loc) && !str_contains(strtolower($loc), 'sample') && !str_contains(strtolower($loc), 'farm pickup point')) {
                    $parts = array_map('trim', explode(',', $loc));
                    foreach ($parts as $p) {
                        if (!empty($p) && !in_array($p, $origins)) {
                            $origins[] = $p;
                        }
                    }
                }
            }
        }

        // 4. Direct farmer or farmer linked through items
        if (empty($origins)) {
            $farmer = $this->farmer;
            if (!$farmer && $this->items && $this->items->isNotEmpty()) {
                $farmer = $this->items->first()?->product?->farmer;
            }

            if ($farmer) {
                if (!empty($farmer->woreda)) {
                    $origins[] = trim($farmer->woreda);
                } elseif (!empty($farmer->region)) {
                    $origins[] = trim($farmer->region);
                }
            }
        }

        if (!empty($origins)) {
            return implode(', ', array_unique($origins));
        }

        return 'Adama';
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
