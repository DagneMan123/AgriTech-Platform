<?php
// app/Models/Harvest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Harvest extends Model
{
    use HasFactory;

    protected $fillable = [
        'crop_id',
        'harvest_date',
        'quantity',
        'quantity_unit',
        'quality_grade',
        'notes'
    ];

    protected $casts = [
        'harvest_date' => 'date',
        'quantity' => 'decimal:2'
    ];

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }
}
