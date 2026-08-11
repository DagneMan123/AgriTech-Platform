<?php
// app/Models/Inventory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'product_id',
        'quantity',
        'min_quantity',
        'max_quantity',
        'location',
        'last_updated'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'min_quantity' => 'decimal:2',
        'max_quantity' => 'decimal:2',
        'last_updated' => 'datetime'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getIsLowAttribute()
    {
        return $this->quantity <= $this->min_quantity;
    }

    public function getIsOverstockedAttribute()
    {
        return $this->quantity >= $this->max_quantity;
    }

    public function getStatusAttribute()
    {
        if ($this->is_low) {
            return 'low';
        } elseif ($this->is_overstocked) {
            return 'overstocked';
        }
        return 'normal';
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'low' => 'Low Stock',
            'overstocked' => 'Overstocked',
            'normal' => 'Normal'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }
}
