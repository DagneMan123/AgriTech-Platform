<?php

namespace App\Http\Requests\Transport;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('transport');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:users,id',
            'pickup_location' => 'required|string|max:500',
            'pickup_date' => 'required|date|after_or_equal:today',
            'delivery_location' => 'required|string|max:500',
            'delivery_date' => 'required|date|after:pickup_date',
            'pickup_latitude' => 'nullable|numeric|between:-90,90',
            'pickup_longitude' => 'nullable|numeric|between:-180,180',
            'delivery_latitude' => 'nullable|numeric|between:-90,90',
            'delivery_longitude' => 'nullable|numeric|between:-180,180',
            'special_handling' => 'nullable|boolean',
            'handling_instructions' => 'nullable|string|max:1000',
            'estimated_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'order_id.required' => 'Order is required.',
            'order_id.exists' => 'Selected order does not exist.',
            'vehicle_id.required' => 'Vehicle is required.',
            'vehicle_id.exists' => 'Selected vehicle does not exist.',
            'driver_id.required' => 'Driver is required.',
            'driver_id.exists' => 'Selected driver does not exist.',
            'pickup_location.required' => 'Pickup location is required.',
            'delivery_location.required' => 'Delivery location is required.',
            'pickup_date.required' => 'Pickup date is required.',
            'delivery_date.required' => 'Delivery date is required.',
        ];
    }
}
