<?php

namespace App\Http\Requests\Financial;

use Illuminate\Foundation\Http\FormRequest;

class InsuranceApplicationRequest extends FormRequest
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
            'insurance_type' => 'required|in:crop,livestock,equipment,property,combined',
            'coverage_amount' => 'required|numeric|min:1000',
            'currency' => 'required|string|max:10',
            'coverage_period_months' => 'required|integer|min:1|max:24',
            'premium_amount' => 'required|numeric|min:0',
            'coverage_start_date' => 'required|date|after_or_equal:today',
            'coverage_end_date' => 'required|date|after:coverage_start_date',
            'insured_items' => 'required|string|max:1000',
            'crop_type' => 'required_if:insurance_type,crop|string|max:255',
            'livestock_type' => 'required_if:insurance_type,livestock|string|max:255',
            'livestock_count' => 'required_if:insurance_type,livestock|integer|min:1',
            'equipment_list' => 'nullable|string|max:1000',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
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
            'insurance_type.required' => 'Insurance type is required.',
            'coverage_amount.required' => 'Coverage amount is required.',
            'coverage_amount.min' => 'Coverage amount must be at least 1000.',
            'coverage_start_date.required' => 'Coverage start date is required.',
            'coverage_end_date.required' => 'Coverage end date is required.',
            'coverage_end_date.after' => 'Coverage end date must be after start date.',
            'insured_items.required' => 'Insured items description is required.',
        ];
    }
}
