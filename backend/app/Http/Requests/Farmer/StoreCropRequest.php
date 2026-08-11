<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCropRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('farmer');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'farm_id' => 'required|exists:farms,id',
            'crop_name' => 'required|string|max:255',
            'crop_type' => 'required|string|max:255',
            'planting_date' => 'required|date',
            'expected_harvest_date' => 'required|date|after:planting_date',
            'area_planted' => 'required|numeric|min:0.01',
            'area_unit' => 'required|in:hectares,acres,square_meters',
            'variety' => 'nullable|string|max:255',
            'seed_source' => 'nullable|string|max:255',
            'fertilizer_type' => 'nullable|string|max:255',
            'pest_control_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'farm_id.required' => 'Farm selection is required.',
            'farm_id.exists' => 'Selected farm does not exist.',
            'crop_name.required' => 'Crop name is required.',
            'planting_date.required' => 'Planting date is required.',
            'expected_harvest_date.required' => 'Expected harvest date is required.',
            'expected_harvest_date.after' => 'Expected harvest date must be after planting date.',
            'area_planted.required' => 'Planted area is required.',
        ];
    }
}
