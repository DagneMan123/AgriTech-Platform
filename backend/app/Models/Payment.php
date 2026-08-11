<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'order_id',
        'user_id',
        'payment_method_id',
        'amount',
        'currency',
        'status',
        'reference_number',
        'transaction_data',
        'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_data' => 'array',
        'paid_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($payment) {
            $payment->payment_number = 'PAY-' . date('Ymd') . '-' . Str::random(8);
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'paid' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'refunded' => 'bg-gray-100 text-gray-800'
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getAmountFormattedAttribute()
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    public function getIsPaidAttribute()
    {
        return $this->status === 'paid';
    }
}
