<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class StoreHarvestRequest extends FormRequest
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
            'crop_id' => 'required|exists:crops,id',
            'harvest_date' => 'required|date',
            'quantity_harvested' => 'required|numeric|min:0.01',
            'quantity_unit' => 'required|in:kg,tons,bags,bundles',
            'quality_grade' => 'nullable|in:A,B,C,D',
            'cost_of_harvest' => 'nullable|numeric|min:0',
            'storage_location' => 'nullable|string|max:255',
            'preservation_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'crop_id.required' => 'Crop selection is required.',
            'crop_id.exists' => 'Selected crop does not exist.',
            'harvest_date.required' => 'Harvest date is required.',
            'quantity_harvested.required' => 'Harvested quantity is required.',
            'quantity_unit.required' => 'Quantity unit is required.',
        ];
    }
}
