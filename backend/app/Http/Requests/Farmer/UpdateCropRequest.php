<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCropRequest extends FormRequest
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
            'crop_name' => 'nullable|string|max:255',
            'crop_type' => 'nullable|string|max:255',
            'planting_date' => 'nullable|date',
            'expected_harvest_date' => 'nullable|date|after:planting_date',
            'area_planted' => 'nullable|numeric|min:0.01',
            'area_unit' => 'nullable|in:hectares,acres,square_meters',
            'variety' => 'nullable|string|max:255',
            'seed_source' => 'nullable|string|max:255',
            'fertilizer_type' => 'nullable|string|max:255',
            'pest_control_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|in:active,harvested,abandoned',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'expected_harvest_date.after' => 'Expected harvest date must be after planting date.',
            'area_unit.in' => 'Selected area unit is invalid.',
        ];
    }
}
