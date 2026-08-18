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
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            'address' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'role' => 'required|string|in:farmer,buyer,supplier,expert,cooperative,financial,transport,admin',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Full name is required.',
            'full_name.string' => 'Full name must be a string.',
            'full_name.max' => 'Full name cannot exceed 255 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone.required' => 'Phone number is required.',
            'phone.string' => 'Phone number must be a string.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'phone.unique' => 'This phone number is already registered.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match.',
            'password_confirmation.required' => 'Password confirmation is required.',
            'address.required' => 'Address is required.',
            'address.string' => 'Address must be a string.',
            'address.max' => 'Address cannot exceed 255 characters.',
            'region.required' => 'Region is required.',
            'region.string' => 'Region must be a string.',
            'region.max' => 'Region cannot exceed 255 characters.',
            'role.required' => 'Role is required.',
            'role.string' => 'Role must be a string.',
            'role.in' => 'Selected role is invalid. Valid roles are: farmer, buyer, supplier, expert, cooperative, financial, transport, admin.',
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
