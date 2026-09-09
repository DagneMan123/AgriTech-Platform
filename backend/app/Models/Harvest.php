<?php

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
        'quantity_harvested',
        'unit',
        'quality_grade',
        'notes',
        'harvest_notes',
        'number_of_workers',
        'labor_cost',
        'storage_method',
        'post_harvest_treatment',
        'market_price_per_unit',
        'total_harvest_value'
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
