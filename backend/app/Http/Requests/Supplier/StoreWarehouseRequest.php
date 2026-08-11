<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
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
            'warehouse_name' => 'required|string|max:255',
            'location' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'capacity' => 'required|integer|min:1',
            'capacity_unit' => 'required|in:units,tons,cubic_meters',
            'warehouse_type' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'storage_features' => 'nullable|array',
            'storage_features.*' => 'string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'warehouse_name.required' => 'Warehouse name is required.',
            'location.required' => 'Warehouse location is required.',
            'capacity.required' => 'Warehouse capacity is required.',
            'warehouse_type.required' => 'Warehouse type is required.',
        ];
    }
}
