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
            'farm_id' => ['required', 'exists:farms,id'],
            'crop_type' => ['required', 'string'],
            'variety' => ['required', 'string'],
            'planting_date' => ['required', 'date'],
            'expected_harvest_date' => ['required', 'date', 'after:planting_date'],
            'area_hectares' => ['required', 'numeric', 'min:0.1'],
            'expected_yield_kg' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
