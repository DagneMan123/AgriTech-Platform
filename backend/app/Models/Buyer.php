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

    // CRITICAL: Prevent eager loading to avoid infinite recursion
    protected $with = [];
    protected $appends = [];

    /**
     * DISABLED: All relationships blocked to prevent recursion during auth
     * Use direct database queries instead
     */
    public function user() { return null; }
    public function orders() { return null; }
    public function reviews() { return null; }
    public function wishlists() { return null; }
    public function cart() { return null; }

    public function getTotalOrdersAttribute()
    {
        // Disabled to prevent recursion - use DB query when needed
        return 0;
    }

    public function getTotalSpentAttribute()
    {
        // Disabled to prevent recursion - use DB query when needed
        return 0;
    }
}
