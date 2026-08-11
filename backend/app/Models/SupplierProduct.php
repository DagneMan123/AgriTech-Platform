<?php
// app/Models/SupplierProduct.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'category_id',
        'name',
        'description',
        'price',
        'price_unit',
        'quantity',
        'quantity_unit',
        'images',
        'specifications',
        'brand',
        'model',
        'warranty',
        'certification',
        'status'
    ];

    protected $casts = [
        'images' => 'array',
        'specifications' => 'array',
        'price' => 'decimal:2',
        'quantity' => 'decimal:2'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function orderItems()
    {
        return $this->hasMany(SupplierOrderItem::class);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'available' => 'Available',
            'sold_out' => 'Sold Out',
            'discontinued' => 'Discontinued'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }
}
