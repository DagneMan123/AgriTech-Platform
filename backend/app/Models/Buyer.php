<?php
// app/Models/Buyer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    use HasFactory;

    protected $table = 'buyers';

    protected $fillable = [
        'user_id',
        'buyer_type',
        'business_name',
        'business_registration',
        'tax_id',
        'business_address',
        'business_phone',
        'bio',
        'total_spent',
        'total_orders',
        'average_rating',
        'verification_status',
        'is_premium'
    ];

    protected $casts = [
        'is_premium' => 'boolean',
        'total_spent' => 'decimal:2',
        'average_rating' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

    public function getTotalOrdersAttribute()
    {
        return $this->orders()->count();
    }

    public function getTotalSpentAttribute()
    {
        return $this->orders()->where('status', 'delivered')->sum('grand_total') ?? 0;
    }
}
