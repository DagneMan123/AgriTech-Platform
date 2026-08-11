<?php

namespace App\Http\Requests\Buyer;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('buyer');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:credit_card,bank_transfer,mobile_money,cash_on_delivery',
            'card_number' => 'required_if:payment_method,credit_card|regex:/^\d{13,19}$/',
            'card_holder_name' => 'required_if:payment_method,credit_card|string|max:255',
            'card_expiry' => 'required_if:payment_method,credit_card|regex:/^\d{2}\/\d{2}$/',
            'card_cvv' => 'required_if:payment_method,credit_card|regex:/^\d{3,4}$/',
            'bank_account_number' => 'required_if:payment_method,bank_transfer|string|max:255',
            'bank_name' => 'required_if:payment_method,bank_transfer|string|max:255',
            'mobile_money_number' => 'required_if:payment_method,mobile_money|string|max:20',
            'transaction_reference' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'order_id.required' => 'Order ID is required.',
            'order_id.exists' => 'Selected order does not exist.',
            'amount.required' => 'Payment amount is required.',
            'payment_method.required' => 'Payment method is required.',
            'card_number.regex' => 'Card number must be valid (13-19 digits).',
            'card_expiry.regex' => 'Card expiry must be in MM/YY format.',
            'card_cvv.regex' => 'Card CVV must be 3-4 digits.',
        ];
    }
}
