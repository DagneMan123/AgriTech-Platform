<?php
// app/Models/Supplier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'user_id',
        'company_name',
        'company_registration',
        'tax_id',
        'license_number',
        'company_address',
        'company_phone',
        'company_website',
        'company_bio',
        'contact_person',
        'supply_type',
        'total_sales',
        'completed_orders',
        'average_rating',
        'verification_status',
        'rejection_reason'
    ];

    protected $casts = [
        'total_sales' => 'decimal:2',
        'average_rating' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function products()
    {
        return $this->hasMany(SupplierProduct::class);
    }
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }
    public function orders()
    {
        return $this->hasMany(SupplierOrder::class);
    }
    public function licenses()
    {
        return $this->hasMany(SupplierLicense::class);
    }
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function getTotalProductsAttribute()
    {
        return $this->products()->count();
    }

    public function getTotalInventoryValueAttribute()
    {
        return $this->inventory()->sum('quantity') ?? 0;
    }
}
