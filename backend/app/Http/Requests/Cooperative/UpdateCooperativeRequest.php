<?php

namespace App\Http\Requests\Cooperative;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCooperativeRequest extends FormRequest
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
        $cooperativeId = $this->route('cooperative');

        return [
            'cooperative_name' => 'nullable|string|max:255',
            'registration_number' => [
                'nullable',
                'string',
                Rule::unique('cooperatives')->ignore($cooperativeId),
                'max:255',
            ],
            'cooperative_type' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'total_members' => 'nullable|integer|min:1',
            'established_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:active,inactive,suspended',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'registration_number.unique' => 'This registration number is already registered.',
            'contact_email.email' => 'Please enter a valid email address.',
            'logo.image' => 'Logo must be a valid image.',
            'logo.max' => 'Logo size must not exceed 2MB.',
        ];
    }
}
