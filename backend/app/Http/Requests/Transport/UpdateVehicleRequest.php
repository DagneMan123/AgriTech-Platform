<?php

namespace App\Http\Requests\Transport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
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
        $vehicleId = $this->route('vehicle');

        return [
            'vehicle_type' => 'nullable|in:bike,car,truck,van,cart,bus,other',
            'vehicle_make' => 'nullable|string|max:255',
            'vehicle_model' => 'nullable|string|max:255',
            'license_plate' => [
                'nullable',
                'string',
                Rule::unique('vehicles')->ignore($vehicleId),
                'max:20',
            ],
            'registration_number' => [
                'nullable',
                'string',
                Rule::unique('vehicles')->ignore($vehicleId),
                'max:255',
            ],
            'capacity' => 'nullable|integer|min:1',
            'capacity_unit' => 'nullable|in:kg,tons,cubic_meters,pieces',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'driver_license_number' => 'nullable|string|max:255',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:255',
            'insurance_expiry_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,maintenance',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'license_plate.unique' => 'This license plate is already registered.',
            'registration_number.unique' => 'This registration number is already registered.',
            'capacity.integer' => 'Capacity must be an integer.',
        ];
    }
}
