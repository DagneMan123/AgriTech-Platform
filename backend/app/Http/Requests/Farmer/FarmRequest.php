<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FarmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['required', 'string'],
            'region' => ['required', 'string'],
            'zone' => ['required', 'string'],
            'woreda' => ['required', 'string'],
            'kebele' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'size_hectares' => ['required', 'numeric', 'min:0.1'],
            'farm_type' => ['required', 'in:crop,livestock,mixed,fishery'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Farm name is required',
            'name.string' => 'Farm name must be a text field',
            'name.max' => 'Farm name cannot exceed 255 characters',
            
            'address.required' => 'Farm address is required',
            'address.string' => 'Farm address must be a text field',
            
            'region.required' => 'Region is required',
            'region.string' => 'Region must be a text field',
            
            'zone.required' => 'Zone is required',
            'zone.string' => 'Zone must be a text field',
            
            'woreda.required' => 'Woreda (district) is required',
            'woreda.string' => 'Woreda must be a text field',
            
            'size_hectares.required' => 'Farm size in hectares is required',
            'size_hectares.numeric' => 'Farm size must be a number',
            'size_hectares.min' => 'Farm size must be at least 0.1 hectares',
            
            'farm_type.required' => 'Farm type is required',
            'farm_type.in' => 'Farm type must be one of: crop, livestock, mixed, or fishery',
            
            'latitude.numeric' => 'Latitude must be a number',
            'latitude.between' => 'Latitude must be between -90 and 90',
            
            'longitude.numeric' => 'Longitude must be a number',
            'longitude.between' => 'Longitude must be between -180 and 180',
        ];
    }

    /**
     * Handle a failed validation attempt.
     * This ensures the error response format is consistent with what the frontend expects
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()->toArray(),
        ], 422));
    }
}
