<?php
// app/Models/Cart.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'product_id', 'quantity', 'options'];

    protected $casts = ['options' => 'array', 'quantity' => 'integer'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->product->price * $this->quantity;
    }

    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 2) . ' ETB';
    }
}
