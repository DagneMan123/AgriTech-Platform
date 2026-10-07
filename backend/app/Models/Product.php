<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'crop_id',
        'category_id',
        'name',
        'description',
        'price',
        'price_unit',
        'quantity',
        'quantity_unit',
        'quality_grade',
        'harvest_date',
        'images',
        'status',
        'is_organic',
        'certification',
        'specifications',
        'views_count',
        'minimum_order_quantity',
        'maximum_order_quantity'
    ];

    protected $casts = [
        'images' => 'array',
        'specifications' => 'array',
        'is_organic' => 'boolean',
        'price' => 'decimal:2',
        'quantity' => 'decimal:2'
    ];

    // CRITICAL: Prevent eager loading to avoid infinite recursion
    protected $with = [];
    protected $appends = [];

    /**
     * DISABLED: All relationships blocked to prevent recursion during auth
     * Use direct database queries instead
     */
    public function farmer() { return null; }
    public function crop() { return null; }
    public function category() { return null; }
    public function orderItems() { return null; }
    public function reviews() { return null; }
    public function cartItems() { return null; }
    public function wishlists() { return null; }
    public function productImages() { return null; }

    public function getAverageRatingAttribute()
    {
        // Disabled to prevent recursion - use DB query when needed
        return 0;
    }

    public function getReviewsCountAttribute()
    {
        // Disabled to prevent recursion - use DB query when needed
        return 0;
    }

    public function getPrimaryImageAttribute()
    {
        return $this->images && count($this->images) > 0
            ? asset('storage/' . $this->images[0])
            : null;
    }

    public function isAvailable()
    {
        return $this->status === 'available' && $this->quantity > 0;
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('quantity', '>', 0);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%");
    }
}
