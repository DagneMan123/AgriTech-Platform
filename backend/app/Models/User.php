<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasApiTokens;

/**
 * SECURITY: This User model is stripped down to prevent infinite recursion
 * during authentication. NO relationships are allowed to load.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'profile_image',
        'location',
        'address',
        'region',
        'zone',
        'woreda',
        'is_active',
        'last_login_at'
    ];

    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime'
    ];
    
    // CRITICAL: NEVER auto-load relationships
    protected $with = [];
    protected $appends = [];

    /**
     * Override __call to completely block all relationship attempts
     * Any attempt to access relationships returns empty collection
     */
    public function __call($method, $parameters)
    {
        // Completely block all relationship method calls
        return collect([]);
    }

    // Role checking methods (safe - no relationships)
    public function isAdmin() { return $this->role === 'admin'; }
    public function isFarmer() { return $this->role === 'farmer'; }
    public function isBuyer() { return $this->role === 'buyer'; }
    public function isSupplier() { return $this->role === 'supplier'; }
    public function isTransport() { return $this->role === 'transport'; }
    public function isExpert() { return $this->role === 'expert'; }
    public function isCooperative() { return $this->role === 'cooperative'; }
    public function isFinancial() { return $this->role === 'financial'; }
}
