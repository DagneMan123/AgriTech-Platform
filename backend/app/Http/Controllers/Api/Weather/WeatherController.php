<?php

namespace App\Http\Controllers\Api\Weather;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherController extends Controller
{
    /**
     * Get weather forecast for coordinates
     */
    public function forecast(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'days' => 'sometimes|integer|min:1|max:14',
        ]);

        $cacheKey = "weather_forecast_{$validated['latitude']}_{$validated['longitude']}";

        $forecast = Cache::remember($cacheKey, 3600, function () use ($validated) {
            try {
                $apiKey = config('services.weather.api_key');
                $days = $validated['days'] ?? 7;

                $response = Http::get('https://api.openweathermap.org/data/2.5/forecast', [
                    'lat' => $validated['latitude'],
                    'lon' => $validated['longitude'],
                    'appid' => $apiKey,
                    'units' => 'metric',
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            } catch (\Exception $e) {
                return null;
            }
        });

        if ($forecast) {
            return response()->json([
                'success' => true,
                'data' => $forecast,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch weather forecast',
        ], 503);
    }

    /**
     * Get current weather
     */
    public function current(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $cacheKey = "weather_current_{$validated['latitude']}_{$validated['longitude']}";

        $weather = Cache::remember($cacheKey, 600, function () use ($validated) {
            try {
                $apiKey = config('services.weather.api_key');

                $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                    'lat' => $validated['latitude'],
                    'lon' => $validated['longitude'],
                    'appid' => $apiKey,
                    'units' => 'metric',
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            } catch (\Exception $e) {
                return null;
            }
        });

        if ($weather) {
            return response()->json([
                'success' => true,
                'data' => $weather,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch current weather',
        ], 503);
    }

    /**
     * Get weather alerts
     */
    public function alerts(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        try {
            $apiKey = config('services.weather.api_key');

            $response = Http::get('https://api.openweathermap.org/data/3.0/alerts', [
                'lat' => $validated['latitude'],
                'lon' => $validated['longitude'],
                'appid' => $apiKey,
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch weather alerts',
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Weather service error',
            ], 500);
        }
    }

    /**
     * Get historical weather data
     */
    public function historical(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $apiKey = config('services.weather.api_key');

            $response = Http::get('https://api.openweathermap.org/data/2.5/history', [
                'lat' => $validated['latitude'],
                'lon' => $validated['longitude'],
                'start' => strtotime($validated['start_date']),
                'end' => strtotime($validated['end_date']),
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
                'message' => 'Failed to fetch historical weather data',
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Weather service error',
            ], 500);
        }
    }
}
