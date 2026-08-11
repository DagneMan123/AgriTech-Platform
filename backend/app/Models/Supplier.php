<?php
// app/Models/Supplier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'supplier_profiles';

    protected $fillable = [
        'user_id',
        'company_name',
        'business_license',
        'address',
        'website',
        'tax_id',
        'bank_account',
        'bank_name',
        'description',
        'is_verified'
    ];

    protected $casts = ['is_verified' => 'boolean'];

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
