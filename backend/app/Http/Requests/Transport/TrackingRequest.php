<?php

namespace App\Http\Requests\Transport;

use Illuminate\Foundation\Http\FormRequest;

class TrackingRequest extends FormRequest
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
            'delivery_id' => 'required|exists:deliveries,id',
            'tracking_number' => 'nullable|string|max:255',
            'current_location_latitude' => 'nullable|numeric|between:-90,90',
            'current_location_longitude' => 'nullable|numeric|between:-180,180',
            'timestamp' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'delivery_id.required' => 'Delivery is required.',
            'delivery_id.exists' => 'Selected delivery does not exist.',
        ];
    }
}
