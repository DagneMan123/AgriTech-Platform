<?php

namespace App\Http\Requests\Buyer;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'order_items' => 'required|array|min:1',
            'order_items.*.product_id' => 'required|exists:products,id',
            'order_items.*.quantity' => 'required|integer|min:1',
            'order_items.*.unit_price' => 'required|numeric|min:0.01',
            'delivery_address' => 'required|string|max:500',
            'delivery_city' => 'required|string|max:255',
            'delivery_state' => 'required|string|max:255',
            'delivery_zip' => 'required|string|max:20',
            'delivery_phone' => 'required|string|max:20',
            'delivery_date' => 'required|date|after_or_equal:today',
            'payment_method' => 'required|in:credit_card,bank_transfer,mobile_money,cash_on_delivery',
            'special_instructions' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'order_items.required' => 'Order items are required.',
            'order_items.min' => 'At least one item is required.',
            'delivery_address.required' => 'Delivery address is required.',
            'payment_method.required' => 'Payment method is required.',
            'delivery_date.required' => 'Delivery date is required.',
        ];
    }
}
