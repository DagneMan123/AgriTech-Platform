<?php

namespace App\Http\Requests\Weather;

use Illuminate\Foundation\Http\FormRequest;

class WeatherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'farm_id' => 'nullable|exists:farms,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_name' => 'nullable|string|max:255',
            'forecast_days' => 'nullable|integer|min:1|max:14',
            'weather_type' => 'nullable|in:current,forecast,historical,hourly',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'farm_id.exists' => 'Selected farm does not exist.',
            'forecast_days.integer' => 'Forecast days must be an integer.',
            'forecast_days.max' => 'Forecast days cannot exceed 14 days.',
        ];
    }
}
