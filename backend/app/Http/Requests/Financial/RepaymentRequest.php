<?php

namespace App\Http\Requests\Financial;

use Illuminate\Foundation\Http\FormRequest;

class RepaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->hasRole('farmer') || auth()->user()->hasRole('financial'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'loan_id' => 'required|exists:loans,id',
            'repayment_amount' => 'required|numeric|min:0.01',
            'repayment_date' => 'required|date|after_or_equal:today',
            'payment_method' => 'required|in:bank_transfer,mobile_money,cash,cheque,card',
            'reference_number' => 'nullable|string|max:255',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'loan_id.required' => 'Loan is required.',
            'loan_id.exists' => 'Selected loan does not exist.',
            'repayment_amount.required' => 'Repayment amount is required.',
            'repayment_amount.min' => 'Repayment amount must be greater than 0.',
            'repayment_date.required' => 'Repayment date is required.',
            'payment_method.required' => 'Payment method is required.',
        ];
    }
}
