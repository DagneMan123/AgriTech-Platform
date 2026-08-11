<?php
// app/Models/FarmWorker.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmWorker extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'name',
        'role',
        'phone',
        'hired_date',
        'salary'
    ];

    protected $casts = [
        'hired_date' => 'date',
        'salary' => 'decimal:2'
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
}
