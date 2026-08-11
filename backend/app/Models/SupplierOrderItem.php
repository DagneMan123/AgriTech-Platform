<?php
// app/Models/SupplierOrderItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_order_id',
        'supplier_product_id',
        'quantity',
        'price',
        'subtotal'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function order()
    {
        return $this->belongsTo(SupplierOrder::class, 'supplier_order_id');
    }
    public function product()
    {
        return $this->belongsTo(SupplierProduct::class, 'supplier_product_id');
    }
}
