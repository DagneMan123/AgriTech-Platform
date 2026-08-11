<?php

namespace App\Http\Requests\Transport;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryStatusRequest extends FormRequest
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
            'status' => 'required|in:pending,in_transit,out_for_delivery,delivered,failed,cancelled',
            'current_location_latitude' => 'nullable|numeric|between:-90,90',
            'current_location_longitude' => 'nullable|numeric|between:-180,180',
            'delivery_timestamp' => 'nullable|date',
            'signature_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'delivery_notes' => 'nullable|string|max:1000',
            'failed_reason' => 'nullable|string|max:500',
            'recipient_name' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Delivery status is required.',
            'status.in' => 'Selected delivery status is invalid.',
            'signature_image.image' => 'Signature image must be a valid image.',
            'signature_image.max' => 'Signature image size must not exceed 2MB.',
        ];
    }
}
