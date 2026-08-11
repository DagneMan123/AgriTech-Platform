<?php
// app/Models/Farmer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    use HasFactory;

    protected $table = 'farmer_profiles';

    protected $fillable = [
        'user_id',
        'farm_name',
        'farm_description',
        'farm_size',
        'address',
        'latitude',
        'longitude',
        'is_verified',
        'profile_image',
        'farm_type'
    ];

    protected $casts = [
        'farm_size' => 'decimal:2',
        'is_verified' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function farms()
    {
        return $this->hasMany(Farm::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
    public function cooperativeMembers()
    {
        return $this->hasMany(CooperativeMember::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getTotalRevenueAttribute()
    {
        return $this->orders()->where('status', 'delivered')->sum('grand_total') ?? 0;
    }

    public function getTotalProductsAttribute()
    {
        return $this->products()->count();
    }

    public function getTotalFarmsAttribute()
    {
        return $this->farms()->count();
    }
}
