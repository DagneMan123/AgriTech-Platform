<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Crop extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'farm_id',
        'crop_type',
        'variety',
        'planting_date',
        'expected_harvest_date',
        'area_hectares',
        'expected_yield_kg',
        'status',
        'notes'
    ];

    protected $casts = [
        'planting_date' => 'date',
        'expected_harvest_date' => 'date',
        'area_hectares' => 'decimal:2',
        'expected_yield_kg' => 'decimal:2'
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function growthRecords()
    {
        return $this->hasMany(CropGrowthRecord::class);
    }

    public function harvests()
    {
        return $this->hasMany(Harvest::class);
    }
}
