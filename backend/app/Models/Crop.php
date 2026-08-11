<?php
// app/Models/Crop.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'crop_category_id',
        'name',
        'variety',
        'planting_date',
        'expected_harvest_date',
        'actual_harvest_date',
        'area_planted',
        'area_unit',
        'expected_yield',
        'actual_yield',
        'yield_unit',
        'status',
        'notes',
        'images'
    ];

    protected $casts = [
        'planting_date' => 'date',
        'expected_harvest_date' => 'date',
        'actual_harvest_date' => 'date',
        'area_planted' => 'decimal:2',
        'expected_yield' => 'decimal:2',
        'actual_yield' => 'decimal:2',
        'images' => 'array'
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
    public function category()
    {
        return $this->belongsTo(CropCategory::class, 'crop_category_id');
    }
    public function images()
    {
        return $this->hasMany(CropImage::class);
    }
    public function growthRecords()
    {
        return $this->hasMany(CropGrowthRecord::class);
    }
    public function harvests()
    {
        return $this->hasMany(Harvest::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'growing' => 'Growing',
            'harvested' => 'Harvested',
            'failed' => 'Failed',
            'fallow' => 'Fallow'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'growing' => 'bg-green-100 text-green-800',
            'harvested' => 'bg-blue-100 text-blue-800',
            'failed' => 'bg-red-100 text-red-800',
            'fallow' => 'bg-yellow-100 text-yellow-800'
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getGrowthDaysAttribute()
    {
        if ($this->planting_date) {
            $endDate = $this->actual_harvest_date ?? $this->expected_harvest_date ?? now();
            return $this->planting_date->diffInDays($endDate);
        }
        return null;
    }

    public function getIsOverdueAttribute()
    {
        if ($this->status === 'growing' && $this->expected_harvest_date) {
            return $this->expected_harvest_date->isPast();
        }
        return false;
    }

    public function scopeGrowing($query)
    {
        return $query->where('status', 'growing');
    }
    public function scopeHarvested($query)
    {
        return $query->where('status', 'harvested');
    }
}
