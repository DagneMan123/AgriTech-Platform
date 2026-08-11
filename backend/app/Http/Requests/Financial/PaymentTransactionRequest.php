<?php

namespace App\Http\Requests\Financial;

use Illuminate\Foundation\Http\FormRequest;

class PaymentTransactionRequest extends FormRequest
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
            'transaction_type' => 'required|in:payment,refund,transfer,deposit,withdrawal',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:10',
            'account_from' => 'required|string|max:255',
            'account_to' => 'required|string|max:255',
            'payment_method' => 'required|in:bank_transfer,mobile_money,cash,cheque,card',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'required|string|max:1000',
            'transaction_date' => 'required|date',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'transaction_type.required' => 'Transaction type is required.',
            'amount.required' => 'Transaction amount is required.',
            'amount.min' => 'Transaction amount must be greater than 0.',
            'account_from.required' => 'Account from is required.',
            'account_to.required' => 'Account to is required.',
            'payment_method.required' => 'Payment method is required.',
            'description.required' => 'Transaction description is required.',
            'transaction_date.required' => 'Transaction date is required.',
        ];
    }
}
