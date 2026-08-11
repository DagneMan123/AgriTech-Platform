<?php
// app/Models/ShoppingCart.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function getTotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->subtotal;
        });
    }

    public function getTotalFormattedAttribute()
    {
        return number_format($this->total, 2) . ' ETB';
    }

    public function getItemCountAttribute()
    {
        return $this->items->sum('quantity');
    }
}
