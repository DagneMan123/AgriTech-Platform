<?php
// app/Models/WeatherForecast.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'region',
        'date',
        'temperature_min',
        'temperature_max',
        'rainfall',
        'humidity',
        'wind_speed',
        'weather_condition',
        'forecast_data',
        'source'
    ];

    protected $casts = [
        'date' => 'date',
        'temperature_min' => 'decimal:2',
        'temperature_max' => 'decimal:2',
        'rainfall' => 'decimal:2',
        'humidity' => 'decimal:2',
        'wind_speed' => 'decimal:2',
        'forecast_data' => 'array'
    ];
}
