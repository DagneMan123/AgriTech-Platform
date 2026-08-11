<?php

namespace App\Http\Requests\Financial;

use Illuminate\Foundation\Http\FormRequest;

class LoanApprovalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('financial');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'loan_id' => 'required|exists:loans,id',
            'approval_status' => 'required|in:approved,rejected,pending,under_review',
            'approved_amount' => 'required_if:approval_status,approved|numeric|min:1',
            'approved_interest_rate' => 'required_if:approval_status,approved|numeric|min:0|max:100',
            'approved_term_months' => 'required_if:approval_status,approved|integer|min:3|max:120',
            'rejection_reason' => 'required_if:approval_status,rejected|string|max:1000',
            'approval_notes' => 'nullable|string|max:1000',
            'disbursement_date' => 'nullable|date|after:today',
            'bank_account_number' => 'required_if:approval_status,approved|string|max:255',
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
            'approval_status.required' => 'Approval status is required.',
            'approved_amount.required_if' => 'Approved amount is required when status is approved.',
            'approved_interest_rate.required_if' => 'Interest rate is required when status is approved.',
            'rejection_reason.required_if' => 'Rejection reason is required when status is rejected.',
        ];
    }
}
