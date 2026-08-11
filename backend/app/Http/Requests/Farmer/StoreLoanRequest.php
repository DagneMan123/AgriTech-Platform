<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
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
            'amount' => 'required|numeric|min:1000|max:10000000',
            'purpose' => 'required|string|in:land_purchase,equipment_purchase,seeds_fertilizer,livestock,farm_infrastructure,working_capital,irrigation_system,other',
            'duration_months' => 'required|integer|min:3|max:60',
            'description' => 'required|string|max:2000',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Loan amount is required.',
            'amount.numeric' => 'Loan amount must be a valid number.',
            'amount.min' => 'Loan amount must be at least ETB 1,000.',
            'amount.max' => 'Loan amount cannot exceed ETB 10,000,000.',
            'purpose.required' => 'Loan purpose is required.',
            'purpose.in' => 'Selected purpose is not valid.',
            'duration_months.required' => 'Loan duration is required.',
            'duration_months.integer' => 'Duration must be a whole number.',
            'duration_months.min' => 'Duration must be at least 3 months.',
            'duration_months.max' => 'Duration cannot exceed 60 months.',
            'description.required' => 'Description is required.',
            'description.max' => 'Description cannot exceed 2000 characters.',
        ];
    }
}
