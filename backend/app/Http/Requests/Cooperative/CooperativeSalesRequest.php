<?php

namespace App\Http\Requests\Cooperative;

use Illuminate\Foundation\Http\FormRequest;

class CooperativeSalesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('cooperative');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'cooperative_id' => 'required|exists:cooperatives,id',
            'product_id' => 'required|exists:products,id',
            'quantity_sold' => 'required|numeric|min:0.01',
            'quantity_unit' => 'required|in:kg,tons,bags,bundles,pieces,liters,boxes',
            'unit_price' => 'required|numeric|min:0.01',
            'total_amount' => 'required|numeric|min:0.01',
            'sale_date' => 'required|date',
            'buyer_name' => 'required|string|max:255',
            'buyer_phone' => 'nullable|string|max:20',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,cheque',
            'payment_status' => 'required|in:pending,partial,completed',
            'delivery_address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cooperative_id.required' => 'Cooperative is required.',
            'cooperative_id.exists' => 'Selected cooperative does not exist.',
            'product_id.required' => 'Product is required.',
            'product_id.exists' => 'Selected product does not exist.',
            'quantity_sold.required' => 'Quantity sold is required.',
            'unit_price.required' => 'Unit price is required.',
            'total_amount.required' => 'Total amount is required.',
            'sale_date.required' => 'Sale date is required.',
            'buyer_name.required' => 'Buyer name is required.',
            'payment_method.required' => 'Payment method is required.',
        ];
    }
}
