<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'refund_number',
        'payment_id',
        'order_id',
        'amount',
        'status',
        'reason',
        'admin_notes',
        'requested_by_user_id',
        'approved_by_user_id',
        'approved_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($refund) {
            if (empty($refund->refund_number)) {
                $refund->refund_number = 'REF-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}
