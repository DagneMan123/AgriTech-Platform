<?php

namespace App\Http\Requests\Marketplace;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'exists:product_categories,id'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit' => ['required', 'string'],
            'harvest_date' => ['required', 'date'],
            'location' => ['required', 'string'],
            'quality_grade' => ['sometimes', 'in:premium,good,standard'],
            'organic_certified' => ['sometimes', 'boolean'],
        ];
    }
}
