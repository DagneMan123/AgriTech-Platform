<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;



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
        'last_login_at',
    ];



    protected $hidden = [
        'password',
        'remember_token',
    ];



    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }



    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isFarmer(): bool
    {
        return $this->role === 'farmer';
    }

    public function isBuyer(): bool
    {
        return $this->role === 'buyer';
    }

    public function isSupplier(): bool
    {
        return $this->role === 'supplier';
    }

    public function isTransport(): bool
    {
        return $this->role === 'transport';
    }

    public function isExpert(): bool
    {
        return $this->role === 'expert';
    }

    public function isCooperative(): bool
    {
        return $this->role === 'cooperative';
    }

    public function isFinancial(): bool
    {
        return $this->role === 'financial';
    }
}
