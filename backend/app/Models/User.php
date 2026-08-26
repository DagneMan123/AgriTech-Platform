<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasApiTokens;

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
    protected $casts = ['email_verified_at' => 'datetime', 'is_active' => 'boolean', 'last_login_at' => 'datetime'];

    public function farmer()
    {
        return $this->hasOne(Farmer::class, 'user_id');
    }
    public function farms()
    {
        return $this->hasMany(Farm::class, 'farmer_id');
    }
    public function buyer()
    {
        return $this->hasOne(Buyer::class);
    }
    public function supplier()
    {
        return $this->hasOne(Supplier::class);
    }
    public function transporter()
    {
        return $this->hasOne(Transporter::class);
    }
    public function expert()
    {
        return $this->hasOne(Expert::class);
    }
    public function cooperative()
    {
        return $this->hasOne(Cooperative::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'farmer_id');
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }
    public function farmerOrders()
    {
        return $this->hasMany(Order::class, 'farmer_id');
    }
    public function cart()
    {
        return $this->hasMany(Cart::class);
    }
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'transporter_id');
    }
    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'expert_id');
    }
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class);
    }
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function getAvatarUrlAttribute()
    {
        return $this->profile_image
            ? asset('storage/' . $this->profile_image)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=4CAF50&color=fff';
    }

    public function getRoleLabelAttribute()
    {
        $roles = [
            'admin' => 'Administrator',
            'farmer' => 'Farmer',
            'buyer' => 'Buyer',
            'supplier' => 'Supplier',
            'transport' => 'Transport Provider',
            'expert' => 'Agricultural Expert',
            'cooperative' => 'Cooperative',
            'financial' => 'Financial Institution'
        ];
        return $roles[$this->role] ?? 'User';
    }

    public function getDashboardRouteAttribute()
    {
        $routes = [
            'admin' => '/admin/dashboard',
            'farmer' => '/farmer/dashboard',
            'buyer' => '/buyer/dashboard',
            'supplier' => '/supplier/dashboard',
            'transport' => '/transport/dashboard',
            'expert' => '/expert/dashboard',
            'cooperative' => '/cooperative/dashboard',
            'financial' => '/financial/dashboard'
        ];
        return $routes[$this->role] ?? '/dashboard';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    public function isFarmer()
    {
        return $this->role === 'farmer';
    }
    public function isBuyer()
    {
        return $this->role === 'buyer';
    }
    public function isSupplier()
    {
        return $this->role === 'supplier';
    }
    public function isTransport()
    {
        return $this->role === 'transport';
    }
    public function isExpert()
    {
        return $this->role === 'expert';
    }
    public function isCooperative()
    {
        return $this->role === 'cooperative';
    }
    public function isFinancial()
    {
        return $this->role === 'financial';
    }
}
