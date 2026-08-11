<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierProductRequest extends FormRequest
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
            'product_name' => 'required|string|max:255',
            'product_description' => 'nullable|string|max:1000',
            'product_category' => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:10',
            'stock_quantity' => 'required|integer|min:1',
            'stock_unit' => 'required|in:kg,tons,bags,bundles,pieces,liters,boxes',
            'supplier_code' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'certification' => 'nullable|string|max:255',
            'minimum_order_quantity' => 'nullable|integer|min:1',
            'lead_time_days' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_name.required' => 'Product name is required.',
            'product_category.required' => 'Product category is required.',
            'unit_price.required' => 'Unit price is required.',
            'unit_price.min' => 'Unit price must be greater than 0.',
            'stock_quantity.required' => 'Stock quantity is required.',
            'stock_unit.required' => 'Stock unit is required.',
            'product_image.image' => 'Product image must be a valid image.',
            'product_image.max' => 'Product image size must not exceed 2MB.',
        ];
    }
}
