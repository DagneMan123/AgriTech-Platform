<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CropActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'crop_id',
        'farm_id',
        'farmer_id',
        'activity_type',
        'activity_date',
        'activity_time',
        'description',
        'quantity',
        'unit',
        'cost',
        'weather',
        'notes'
    ];

    protected $casts = [
        'activity_date' => 'date',
        'quantity' => 'decimal:2',
        'cost' => 'decimal:2'
    ];

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }
}
