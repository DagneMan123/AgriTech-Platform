<?php
// app/Models/MarketPrice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'category',
        'city',
        'region',
        'price',
        'price_unit',
        'quality',
        'source',
        'is_verified'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_verified' => 'boolean'
    ];

    public $timestamps = false;

    public function getPriceFormattedAttribute()
    {
        return number_format($this->price, 2) . ' ' . ($this->price_unit ?? 'ETB/kg');
    }
}
