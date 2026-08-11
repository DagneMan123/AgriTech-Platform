<?php

namespace App\Http\Requests\Buyer;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'delivery_address' => ['required', 'string'],
            'delivery_region' => ['required', 'string'],
            'delivery_zone' => ['required', 'string'],
            'delivery_woreda' => ['required', 'string'],
            'delivery_latitude' => ['required', 'numeric'],
            'delivery_longitude' => ['required', 'numeric'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
