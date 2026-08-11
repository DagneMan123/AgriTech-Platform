<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'platform_name' => 'nullable|string|max:255',
            'platform_email' => 'nullable|email|max:255',
            'platform_phone' => 'nullable|string|max:20',
            'platform_address' => 'nullable|string|max:500',
            'platform_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'maintenance_mode' => 'nullable|boolean',
            'currency' => 'nullable|string|max:10',
            'default_language' => 'nullable|string|max:10',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'platform_email.email' => 'Please enter a valid email address.',
            'commission_rate.numeric' => 'Commission rate must be a number.',
            'platform_logo.image' => 'Platform logo must be a valid image.',
            'platform_logo.max' => 'Platform logo size must not exceed 2MB.',
        ];
    }
}
