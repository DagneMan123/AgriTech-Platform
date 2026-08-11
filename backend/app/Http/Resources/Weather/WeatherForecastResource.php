<?php

namespace App\Http\Resources\Weather;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WeatherForecastResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'farm_id' => $this->farm_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location_name' => $this->location_name,
            'temperature' => $this->temperature,
            'temperature_unit' => $this->temperature_unit,
            'humidity' => $this->humidity,
            'wind_speed' => $this->wind_speed,
            'wind_direction' => $this->wind_direction,
            'rainfall' => $this->rainfall,
            'UV_index' => $this->UV_index,
            'weather_condition' => $this->weather_condition,
            'weather_description' => $this->weather_description,
            'visibility' => $this->visibility,
            'pressure' => $this->pressure,
            'weather_icon' => $this->weather_icon,
            'forecast_date' => $this->forecast_date,
            'recorded_at' => $this->recorded_at,
            'alerts' => $this->alerts,
        ];
    }
}
