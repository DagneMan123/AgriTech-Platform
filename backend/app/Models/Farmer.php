<?php
// app/Models/Farmer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    use HasFactory;

    protected $table = 'farmers';

    protected $fillable = [
        'user_id',
        'farmer_registration_number',
        'farm_name',
        'region',
        'zone',
        'woreda',
        'kebele',
        'farm_size',
        'farm_type',
        'years_of_experience',
        'bio',
        'bank_account',
        'bank_name',
        'cooperative_name',
        'total_earnings',
        'completed_orders',
        'average_rating',
        'verification_status',
        'rejection_reason',
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
        return $this->hasMany(Review::class);
    }

    public function cooperativeMembers()
    {
        return $this->hasMany(CooperativeMember::class);
    }

    public function cropActivities()
    {
        return $this->hasMany(CropActivity::class);
    }
}
