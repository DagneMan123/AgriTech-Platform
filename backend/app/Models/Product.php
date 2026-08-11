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

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }
    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
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
