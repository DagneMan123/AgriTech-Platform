<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'harvest_id' => 'required|exists:harvests,id',
            'product_name' => 'required|string|max:255',
            'product_description' => 'nullable|string|max:1000',
            'quantity_available' => 'required|numeric|min:0.01',
            'quantity_unit' => 'required|in:kg,tons,bags,bundles,pieces',
            'unit_price' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:10',
            'quality_grade' => 'nullable|in:A,B,C,D',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'nullable|string|max:255',
            'certification' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'harvest_id.required' => 'Harvest selection is required.',
            'harvest_id.exists' => 'Selected harvest does not exist.',
            'product_name.required' => 'Product name is required.',
            'quantity_available.required' => 'Quantity available is required.',
            'unit_price.required' => 'Unit price is required.',
            'unit_price.min' => 'Unit price must be greater than 0.',
            'product_image.image' => 'Product image must be a valid image.',
            'product_image.max' => 'Product image size must not exceed 2MB.',
        ];
    }
}
