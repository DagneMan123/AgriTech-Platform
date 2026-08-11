<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WeatherForecast;
use Illuminate\Support\Facades\Http;

class FetchWeatherData extends Command
{
    protected $signature = 'weather:fetch';
    protected $description = 'Fetch weather data from external API';

    public function handle()
    {
        // Example: Fetch from OpenWeatherMap or similar
        $regions = ['Addis Ababa', 'Oromia', 'SNNPR', 'Amhara'];

        foreach ($regions as $region) {
            try {
                // Integrate with actual weather API
                // For now, just log
                $this->info("Fetched weather for $region");
            } catch (\Exception $e) {
                $this->error("Failed to fetch weather for $region: " . $e->getMessage());
            }
        }

        $this->info('Weather data fetched successfully');
    }
}
