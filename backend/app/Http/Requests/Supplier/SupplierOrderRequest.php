<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class SupplierOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('supplier');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'order_items' => 'required|array|min:1',
            'order_items.*.product_id' => 'required|exists:supplier_products,id',
            'order_items.*.quantity' => 'required|integer|min:1',
            'order_items.*.unit_price' => 'required|numeric|min:0.01',
            'buyer_name' => 'required|string|max:255',
            'buyer_email' => 'required|email|max:255',
            'buyer_phone' => 'required|string|max:20',
            'delivery_address' => 'required|string|max:500',
            'delivery_city' => 'required|string|max:255',
            'delivery_state' => 'required|string|max:255',
            'delivery_zip' => 'required|string|max:20',
            'delivery_date' => 'required|date|after_or_equal:today',
            'payment_terms' => 'required|in:prepaid,cash_on_delivery,installment',
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
            'buyer_name.required' => 'Buyer name is required.',
            'buyer_email.required' => 'Buyer email is required.',
            'buyer_email.email' => 'Please enter a valid email address.',
            'buyer_phone.required' => 'Buyer phone is required.',
            'delivery_address.required' => 'Delivery address is required.',
            'delivery_date.required' => 'Delivery date is required.',
            'payment_terms.required' => 'Payment terms are required.',
        ];
    }
}
