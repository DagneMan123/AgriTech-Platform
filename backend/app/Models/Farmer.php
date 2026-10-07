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

    // CRITICAL: Prevent eager loading to avoid infinite recursion
    protected $with = [];
    protected $appends = [];

    /**
     * DISABLED: All relationships blocked to prevent recursion
     */
    public function user() { return null; }
    public function farms() { return null; }
    public function products() { return null; }
    public function orders() { return null; }
    public function reviews() { return null; }
    public function cooperativeMembers() { return null; }
    public function cropActivities() { return null; }
}
