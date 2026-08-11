<?php
// app/Models/FarmEquipment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmEquipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'name',
        'type',
        'model',
        'quantity',
        'condition'
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
}
