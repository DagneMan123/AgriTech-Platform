<?php

namespace App\Services;

use App\Models\WeatherForecast;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function getWeatherForRegion(string $region)
    {
        return WeatherForecast::where('region', $region)
            ->where('forecast_date', '>=', now())
            ->orderBy('forecast_date')
            ->limit(7)
            ->get();
    }

    public function fetchAndStoreWeatherData()
    {
        $regions = ['Addis Ababa', 'Oromia', 'SNNPR', 'Amhara'];

        foreach ($regions as $region) {
            try {
                // Integration point for actual weather API
                $this->generateSampleWeatherData($region);
            } catch (\Exception $e) {
                logger()->error("Weather fetch failed for $region: " . $e->getMessage());
            }
        }
    }

    private function generateSampleWeatherData(string $region)
    {
        for ($i = 0; $i < 7; $i++) {
            WeatherForecast::create([
                'region' => $region,
                'forecast_date' => now()->addDays($i)->format('Y-m-d'),
                'temperature_min' => rand(15, 20),
                'temperature_max' => rand(25, 35),
                'precipitation_mm' => rand(0, 50),
                'humidity_percent' => rand(40, 90),
                'wind_speed_kmh' => rand(5, 25),
                'weather_condition' => $this->getRandomCondition(),
            ]);
        }
    }

    private function getRandomCondition()
    {
        $conditions = ['sunny', 'cloudy', 'rainy', 'partly_cloudy', 'stormy'];
        return $conditions[array_rand($conditions)];
    }
}
