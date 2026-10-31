<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketPrice extends Model
{
    use HasFactory;

    protected $table = 'market_prices';

    protected $fillable = [
        'crop_name',
        'market_location',
        'market_region',
        'price_per_unit',
        'unit',
        'price_trend',
        'price_date',
        'notes',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'price_date' => 'date',
    ];

    /**
     * Accessor for formatted price with currency.
     */
    public function getPriceFormattedAttribute(): string
    {
        return 'ETB ' . number_format((float)$this->price_per_unit, 2) . ' / ' . ($this->unit ?? 'kg');
    }

    /**
     * Backward-compatible aliases for legacy frontend/controller references.
     */
    public function getProductNameAttribute(): string
    {
        return $this->crop_name ?? '';
    }

    public function getPriceAttribute(): float
    {
        return (float)($this->price_per_unit ?? 0);
    }
}
