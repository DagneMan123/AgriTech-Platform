<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'product_name' => 'nullable|string|max:255',
            'product_description' => 'nullable|string|max:1000',
            'quantity_available' => 'nullable|numeric|min:0.01',
            'quantity_unit' => 'nullable|in:kg,tons,bags,bundles,pieces',
            'unit_price' => 'nullable|numeric|min:0.01',
            'currency' => 'nullable|string|max:10',
            'quality_grade' => 'nullable|in:A,B,C,D',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'nullable|string|max:255',
            'certification' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'status' => 'nullable|in:available,unavailable,discontinued',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'unit_price.numeric' => 'Unit price must be a number.',
            'unit_price.min' => 'Unit price must be greater than 0.',
            'product_image.image' => 'Product image must be a valid image.',
            'product_image.max' => 'Product image size must not exceed 2MB.',
        ];
    }
}
