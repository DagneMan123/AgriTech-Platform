<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHarvestRequest extends FormRequest
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
            'harvest_date' => 'nullable|date',
            'quantity_harvested' => 'nullable|numeric|min:0.01',
            'quantity_unit' => 'nullable|in:kg,tons,bags,bundles',
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
            'quantity_harvested.numeric' => 'Harvested quantity must be a number.',
            'quality_grade.in' => 'Selected quality grade is invalid.',
        ];
    }
}
