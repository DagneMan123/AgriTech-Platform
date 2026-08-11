<?php
// app/Models/Buyer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    use HasFactory;

    protected $table = 'buyer_profiles';

    protected $fillable = [
        'user_id',
        'company_name',
        'business_type',
        'address',
        'preferred_payment_method',
        'is_verified'
    ];

    protected $casts = ['is_verified' => 'boolean'];

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
