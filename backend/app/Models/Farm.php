<?php
// app/Models/Farm.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farm extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'name',
        'description',
        'location',
        'region',
        'zone',
        'woreda',
        'size',
        'size_unit',
        'soil_type',
        'status',
        'images',
        'latitude',
        'longitude',
        'altitude',
        'irrigation_type',
        'water_source'
    ];

    protected $casts = [
        'size' => 'decimal:2',
        'images' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'altitude' => 'decimal:2'
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }
    public function crops()
    {
        return $this->hasMany(Crop::class);
    }
    public function images()
    {
        return $this->hasMany(FarmImage::class);
    }
    public function documents()
    {
        return $this->hasMany(FarmDocument::class);
    }
    public function equipment()
    {
        return $this->hasMany(FarmEquipment::class);
    }
    public function workers()
    {
        return $this->hasMany(FarmWorker::class);
    }
    public function activities()
    {
        return $this->hasMany(FarmActivity::class);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'fallow' => 'Fallow'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
            'fallow' => 'bg-yellow-100 text-yellow-800'
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getPrimaryImageAttribute()
    {
        return $this->images && count($this->images) > 0
            ? asset('storage/' . $this->images[0])
            : null;
    }

    public function getTotalCropsAttribute()
    {
        return $this->crops()->count();
    }

    public function getActiveCropsAttribute()
    {
        return $this->crops()->where('status', 'growing')->count();
    }

    public function getHarvestedCropsAttribute()
    {
        return $this->crops()->where('status', 'harvested')->count();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }
}
