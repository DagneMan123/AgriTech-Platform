<?php

namespace App\Http\Requests\Financial;

use Illuminate\Foundation\Http\FormRequest;

class LoanApplicationRequest extends FormRequest
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
            'loan_type' => 'required|in:agricultural,seasonal,emergency,business_expansion',
            'loan_amount' => 'required|numeric|min:1000',
            'currency' => 'required|string|max:10',
            'loan_purpose' => 'required|string|max:1000',
            'requested_term_months' => 'required|integer|min:3|max:120',
            'collateral_type' => 'nullable|string|max:255',
            'collateral_value' => 'nullable|numeric|min:0',
            'farm_id' => 'required|exists:farms,id',
            'annual_income' => 'required|numeric|min:0',
            'credit_score' => 'nullable|integer|min:0|max:1000',
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
            'loan_type.required' => 'Loan type is required.',
            'loan_amount.required' => 'Loan amount is required.',
            'loan_amount.min' => 'Loan amount must be at least 1000.',
            'loan_purpose.required' => 'Loan purpose is required.',
            'requested_term_months.required' => 'Loan term in months is required.',
            'farm_id.required' => 'Farm selection is required.',
            'farm_id.exists' => 'Selected farm does not exist.',
            'annual_income.required' => 'Annual income is required.',
        ];
    }
}
