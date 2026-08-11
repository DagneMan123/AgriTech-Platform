<?php

namespace App\Http\Requests\Marketplace;

use Illuminate\Foundation\Http\FormRequest;

class ProductSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search_query' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:500',
            'sort_by' => 'nullable|in:newest,price_asc,price_desc,rating,popularity',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'quality_grade' => 'nullable|in:A,B,C,D',
            'certification' => 'nullable|string|max:255',
            'availability' => 'nullable|in:available,pre_order,out_of_stock',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'Selected category does not exist.',
            'min_price.numeric' => 'Minimum price must be a number.',
            'max_price.numeric' => 'Maximum price must be a number.',
        ];
    }
}
