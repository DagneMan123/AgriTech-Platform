<?php
// app/Models/FarmActivity.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'activity_type',
        'description',
        'activity_date',
        'details'
    ];

    protected $casts = [
        'activity_date' => 'date',
        'details' => 'array'
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function getActivityTypeLabelAttribute()
    {
        $types = [
            'planting' => 'Planting',
            'watering' => 'Watering',
            'fertilizing' => 'Fertilizing',
            'pesticide' => 'Pesticide Application',
            'harvesting' => 'Harvesting',
            'weeding' => 'Weeding',
            'other' => 'Other'
        ];
        return $types[$this->activity_type] ?? ucfirst($this->activity_type);
    }
}
