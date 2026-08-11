<?php
// app/Models/Invoice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'order_id',
        'user_id',
        'total_amount',
        'tax_amount',
        'grand_total',
        'status',
        'due_date',
        'paid_at',
        'items'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'items' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($invoice) {
            $invoice->invoice_number = 'INV-' . date('Ymd') . '-' . Str::random(8);
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

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'unpaid' => 'Unpaid',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'unpaid' => 'bg-yellow-100 text-yellow-800',
            'paid' => 'bg-green-100 text-green-800',
            'overdue' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800'
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }
}
