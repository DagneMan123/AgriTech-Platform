<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Weather Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for weather data fetching and integration with external APIs
    |
    */

    'provider' => env('WEATHER_PROVIDER', 'openweathermap'),

    'openweathermap' => [
        'api_key' => env('OPENWEATHERMAP_API_KEY', ''),
        'units' => env('WEATHER_UNITS', 'metric'), // metric, imperial, standard
        'language' => env('WEATHER_LANGUAGE', 'en'),
    ],

    'weatherapi' => [
        'api_key' => env('WEATHERAPI_API_KEY', ''),
        'aqi' => env('WEATHERAPI_AQI', 'no'), // yes, no
    ],

    'forecast' => [
        'days' => env('WEATHER_FORECAST_DAYS', 7),
        'interval_hours' => env('WEATHER_FORECAST_INTERVAL', 6),
        'cache_minutes' => env('WEATHER_CACHE_MINUTES', 60),
    ],

    'alerts' => [
        'temperature_min' => env('WEATHER_ALERT_TEMP_MIN', 0),
        'temperature_max' => env('WEATHER_ALERT_TEMP_MAX', 40),
        'rainfall_threshold' => env('WEATHER_ALERT_RAINFALL', 50), // mm
        'wind_speed_threshold' => env('WEATHER_ALERT_WIND', 40), // km/h
    ],

    'update_frequency' => env('WEATHER_UPDATE_FREQUENCY', 'every_6_hours'), // hourly, every_6_hours, daily
];
