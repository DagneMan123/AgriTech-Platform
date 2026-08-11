<?php

namespace App\Http\Requests\Cooperative;

use Illuminate\Foundation\Http\FormRequest;

class StoreCooperativeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('cooperative');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'cooperative_name' => 'required|string|max:255',
            'registration_number' => 'required|string|unique:cooperatives|max:255',
            'cooperative_type' => 'required|string|max:255',
            'location' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'total_members' => 'nullable|integer|min:1',
            'established_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'contact_person' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cooperative_name.required' => 'Cooperative name is required.',
            'registration_number.required' => 'Registration number is required.',
            'registration_number.unique' => 'This registration number is already registered.',
            'cooperative_type.required' => 'Cooperative type is required.',
            'location.required' => 'Cooperative location is required.',
            'contact_person.required' => 'Contact person is required.',
            'contact_phone.required' => 'Contact phone is required.',
            'contact_email.required' => 'Contact email is required.',
            'contact_email.email' => 'Please enter a valid email address.',
            'logo.image' => 'Logo must be a valid image.',
            'logo.max' => 'Logo size must not exceed 2MB.',
        ];
    }
}
