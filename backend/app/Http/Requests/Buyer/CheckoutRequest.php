<?php

namespace App\Http\Requests\Buyer;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
            'cart_items' => 'required|array|min:1',
            'cart_items.*.product_id' => 'required|exists:products,id',
            'cart_items.*.quantity' => 'required|integer|min:1',
            'delivery_address' => 'required|string|max:500',
            'delivery_city' => 'required|string|max:255',
            'delivery_state' => 'required|string|max:255',
            'delivery_zip' => 'required|string|max:20',
            'delivery_phone' => 'required|string|max:20',
            'delivery_date' => 'required|date|after_or_equal:today',
            'special_instructions' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cart_items.required' => 'Cart items are required.',
            'cart_items.min' => 'At least one item is required in cart.',
            'delivery_address.required' => 'Delivery address is required.',
            'delivery_city.required' => 'Delivery city is required.',
            'delivery_state.required' => 'Delivery state is required.',
            'delivery_zip.required' => 'Delivery zip code is required.',
            'delivery_phone.required' => 'Delivery phone is required.',
            'delivery_date.required' => 'Delivery date is required.',
        ];
    }
}
