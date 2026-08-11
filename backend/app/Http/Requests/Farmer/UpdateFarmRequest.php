<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFarmRequest extends FormRequest
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
            'farm_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'total_area' => 'nullable|numeric|min:0.01',
            'area_unit' => 'nullable|in:hectares,acres,square_meters',
            'farm_type' => 'nullable|string|max:255',
            'soil_type' => 'nullable|string|max:255',
            'water_source' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'total_area.numeric' => 'Total farm area must be a number.',
            'area_unit.in' => 'Selected area unit is invalid.',
        ];
    }
}
