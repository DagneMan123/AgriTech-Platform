<?php
// app/Models/Cooperative.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cooperative extends Model
{
    use HasFactory;

    protected $table = 'cooperatives';

    protected $fillable = [
        'user_id',
        'cooperative_name',
        'description',
        'registration_number',
        'location',
        'region',
        'zone',
        'woreda',
        'phone',
        'email',
        'logo',
        'is_verified'
    ];

    protected $casts = ['is_verified' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function members()
    {
        return $this->hasMany(CooperativeMember::class);
    }
    public function sales()
    {
        return $this->hasMany(CooperativeSale::class);
    }

    public function getTotalMembersAttribute()
    {
        return $this->members()->count();
    }

    public function getActiveMembersAttribute()
    {
        return $this->members()->where('status', 'active')->count();
    }
}
