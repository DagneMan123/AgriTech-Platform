<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class CropRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'farm_id' => ['required', 'integer', 'exists:farms,id'],
            'crop_type' => ['required', 'string', 'max:255'],
            'variety' => ['nullable', 'string', 'max:255'],
            'planting_date' => ['required', 'date_format:Y-m-d'],
            'expected_harvest_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:planting_date'],
            'area_hectares' => ['required', 'numeric', 'min:0.1', 'max:99999.99'],
            'expected_yield_kg' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'status' => ['nullable', 'in:planning,planted,growing,ready_for_harvest,harvested'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages()
    {
        return [
            'farm_id.required' => 'Farm is required',
            'farm_id.exists' => 'Selected farm does not exist',
            'crop_type.required' => 'Crop type is required',
            'planting_date.required' => 'Planting date is required',
            'planting_date.date_format' => 'Planting date must be in Y-m-d format',
            'expected_harvest_date.date_format' => 'Harvest date must be in Y-m-d format',
            'expected_harvest_date.after_or_equal' => 'Harvest date must be after or equal to planting date',
            'area_hectares.required' => 'Area in hectares is required',
            'area_hectares.min' => 'Area must be at least 0.1 hectares',
        ];
    }
}
