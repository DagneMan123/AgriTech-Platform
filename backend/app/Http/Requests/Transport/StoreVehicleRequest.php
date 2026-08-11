<?php

namespace App\Http\Requests\Transport;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
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
            'vehicle_type' => 'required|in:bike,car,truck,van,cart,bus,other',
            'vehicle_make' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'license_plate' => 'required|string|unique:vehicles|max:20',
            'registration_number' => 'required|string|unique:vehicles|max:255',
            'capacity' => 'required|integer|min:1',
            'capacity_unit' => 'required|in:kg,tons,cubic_meters,pieces',
            'year_manufactured' => 'required|integer|min:1900|max:' . date('Y'),
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'required|string|max:20',
            'driver_license_number' => 'required|string|max:255',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:255',
            'insurance_expiry_date' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'vehicle_type.required' => 'Vehicle type is required.',
            'vehicle_make.required' => 'Vehicle make is required.',
            'vehicle_model.required' => 'Vehicle model is required.',
            'license_plate.required' => 'License plate is required.',
            'license_plate.unique' => 'This license plate is already registered.',
            'registration_number.required' => 'Registration number is required.',
            'registration_number.unique' => 'This registration number is already registered.',
            'capacity.required' => 'Vehicle capacity is required.',
            'year_manufactured.required' => 'Year manufactured is required.',
            'driver_name.required' => 'Driver name is required.',
        ];
    }
}
