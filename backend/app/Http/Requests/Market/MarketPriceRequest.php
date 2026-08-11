<?php

namespace App\Http\Requests\Market;

use Illuminate\Foundation\Http\FormRequest;

class MarketPriceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'product_id' => 'nullable|exists:products,id',
            'product_category' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'market_type' => 'nullable|in:wholesale,retail,auction,all',
            'price_range' => 'nullable|in:all,budget,mid_range,premium',
            'sort_by' => 'nullable|in:newest,price_asc,price_desc,demand',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.exists' => 'Selected product does not exist.',
        ];
    }
}
