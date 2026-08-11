<?php

namespace App\Http\Requests\Cooperative;

use Illuminate\Foundation\Http\FormRequest;

class MemberRequest extends FormRequest
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
            'cooperative_id' => 'required|exists:cooperatives,id',
            'member_name' => 'required|string|max:255',
            'member_email' => 'required|email|max:255',
            'member_phone' => 'required|string|max:20',
            'member_role' => 'required|in:member,treasurer,secretary,chairman,vice_chairman',
            'date_joined' => 'required|date',
            'membership_status' => 'required|in:active,inactive,suspended',
            'contribution_amount' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:500',
            'national_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cooperative_id.required' => 'Cooperative is required.',
            'cooperative_id.exists' => 'Selected cooperative does not exist.',
            'member_name.required' => 'Member name is required.',
            'member_email.required' => 'Member email is required.',
            'member_email.email' => 'Please enter a valid email address.',
            'member_phone.required' => 'Member phone is required.',
            'member_role.required' => 'Member role is required.',
            'date_joined.required' => 'Date joined is required.',
        ];
    }
}
