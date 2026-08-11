<?php
// app/Models/CropGrowthRecord.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CropGrowthRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'crop_id',
        'record_date',
        'height',
        'growth_stage',
        'observations',
        'images'
    ];

    protected $casts = [
        'record_date' => 'date',
        'height' => 'decimal:2',
        'images' => 'array'
    ];

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }
}
