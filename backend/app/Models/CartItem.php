<?php
// app/Models/CartItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'options'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'options' => 'array'
    ];

    public function cart()
    {
        return $this->belongsTo(ShoppingCart::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->product->price * $this->quantity;
    }
}
