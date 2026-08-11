<?php
// app/Models/CooperativeSale.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CooperativeSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'cooperative_id',
        'product_id',
        'buyer_id',
        'quantity',
        'price',
        'total',
        'sale_date',
        'status'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'quantity' => 'decimal:2'
    ];

    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }
}
