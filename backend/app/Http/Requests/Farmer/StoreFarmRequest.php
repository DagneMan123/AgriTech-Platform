<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class StoreFarmRequest extends FormRequest
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
            'farm_name' => 'required|string|max:255',
            'location' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'total_area' => 'required|numeric|min:0.01',
            'area_unit' => 'required|in:hectares,acres,square_meters',
            'farm_type' => 'required|string|max:255',
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
            'farm_name.required' => 'Farm name is required.',
            'location.required' => 'Farm location is required.',
            'total_area.required' => 'Total farm area is required.',
            'total_area.numeric' => 'Total farm area must be a number.',
            'area_unit.required' => 'Area unit is required.',
            'farm_type.required' => 'Farm type is required.',
        ];
    }
}
