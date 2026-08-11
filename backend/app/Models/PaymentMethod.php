<?php
// app/Models/PaymentMethod.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'configuration',
        'is_active'
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean'
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
