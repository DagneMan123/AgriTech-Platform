<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $role = strtolower($this->input('role', ''));
        
        // Base rules for all roles
        $rules = [
            'role' => 'required|string|in:farmer,buyer,supplier,expert,cooperative,financial,transport',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            'region' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ];

        // Role-specific rules
        switch ($role) {
            case 'farmer':
                $rules = array_merge($rules, [
                    'full_name' => 'required|string|max:255',
                    'email' => 'nullable|email|unique:users,email',
                    'kebele_id_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                ]);
                break;

            case 'buyer':
                $rules = array_merge($rules, [
                    'business_name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'trade_license_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                    'tin_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                ]);
                break;

            case 'supplier':
                $rules = array_merge($rules, [
                    'business_name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'business_license_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                    'sectoral_clearance_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                ]);
                break;

            case 'transport':
                $rules = array_merge($rules, [
                    'company_name' => 'required|string|max:255',
                    'email' => 'nullable|email|unique:users,email',
                    'driving_license_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                    'vehicle_bluebook_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                ]);
                break;

            case 'expert':
                $rules = array_merge($rules, [
                    'full_name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'degree_certificate_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                ]);
                break;

            case 'financial':
                $rules = array_merge($rules, [
                    'institution_name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'nbe_license_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                ]);
                break;

            case 'cooperative':
                $rules = array_merge($rules, [
                    'cooperative_name' => 'required|string|max:255',
                    'email' => 'nullable|email|unique:users,email',
                    'registration_certificate_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                ]);
                break;
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Full name is required.',
            'business_name.required' => 'Business name is required.',
            'company_name.required' => 'Company name is required.',
            'cooperative_name.required' => 'Cooperative name is required.',
            'institution_name.required' => 'Institution name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone.required' => 'Phone number is required.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'phone.unique' => 'This phone number is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match.',
            'address.required' => 'Address is required.',
            'region.required' => 'Region is required.',
            'role.required' => 'Role is required.',
            'role.in' => 'Selected role is invalid.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => $this->full_name ?? $this->name,
            'address' => $this->address ?? $this->location,
        ]);
    }
}
