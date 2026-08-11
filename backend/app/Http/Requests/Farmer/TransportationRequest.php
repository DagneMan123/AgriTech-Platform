<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class TransportationRequest extends FormRequest
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
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'pickup_location' => 'required|string|max:500',
            'delivery_location' => 'required|string|max:500',
            'pickup_date' => 'required|date|after_or_equal:today',
            'delivery_date' => 'required|date|after:pickup_date',
            'vehicle_type' => 'required|in:bike,car,truck,van,cart,other',
            'special_handling' => 'nullable|boolean',
            'handling_instructions' => 'nullable|string|max:1000',
            'estimated_cost' => 'nullable|numeric|min:0',
            'contact_person' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Product selection is required.',
            'product_id.exists' => 'Selected product does not exist.',
            'quantity.required' => 'Quantity is required.',
            'pickup_location.required' => 'Pickup location is required.',
            'delivery_location.required' => 'Delivery location is required.',
            'pickup_date.required' => 'Pickup date is required.',
            'delivery_date.required' => 'Delivery date is required.',
            'delivery_date.after' => 'Delivery date must be after pickup date.',
            'vehicle_type.required' => 'Vehicle type is required.',
        ];
    }
}
