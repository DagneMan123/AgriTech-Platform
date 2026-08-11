<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    /**
     * Get weather forecast for farmer location
     */
    public function forecast(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'days' => 'sometimes|integer|min:1|max:14',
        ]);

        try {
            // Call weather API (example with OpenWeatherMap)
            $apiKey = config('services.weather.api_key');
            $days = $validated['days'] ?? 7;

            $response = Http::get('https://api.openweathermap.org/data/2.5/forecast', [
                'lat' => $validated['latitude'],
                'lon' => $validated['longitude'],
                'appid' => $apiKey,
                'units' => 'metric',
            ]);

            if ($response->successful()) {
                // Store weather data
                \App\Models\WeatherForecast::create([
                    'user_id' => Auth::id(),
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'data' => $response->json(),
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch weather data',
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Weather service error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current weather
     */
    public function current(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        try {
            $apiKey = config('services.weather.api_key');

            $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                'lat' => $validated['latitude'],
                'lon' => $validated['longitude'],
                'appid' => $apiKey,
                'units' => 'metric',
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch current weather',
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Weather service error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get weather alerts
     */
    public function alerts(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        try {
            $apiKey = config('services.weather.api_key');

            $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                'lat' => $validated['latitude'],
                'lon' => $validated['longitude'],
                'appid' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $alerts = [];

                // Parse alerts from response
                if (isset($data['alerts'])) {
                    $alerts = $data['alerts'];
                }

                return response()->json([
                    'success' => true,
                    'data' => $alerts,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch weather alerts',
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Weather service error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get historical weather data
     */
    public function historical(Request $request)
    {
        $forecasts = \App\Models\WeatherForecast::where('user_id', Auth::id())
            ->latest()
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $forecasts,
        ]);
    }
}
