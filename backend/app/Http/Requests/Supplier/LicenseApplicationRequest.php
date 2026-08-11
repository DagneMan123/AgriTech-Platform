<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class LicenseApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('supplier');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'company_registration_number' => 'required|string|max:255|unique:supplier_licenses',
            'business_type' => 'required|string|max:255',
            'company_address' => 'required|string|max:500',
            'company_phone' => 'required|string|max:20',
            'company_email' => 'required|email|max:255',
            'contact_person' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'tax_identification_number' => 'nullable|string|max:255',
            'business_license_document' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'tax_certificate_document' => 'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'years_in_business' => 'nullable|integer|min:0',
            'number_of_employees' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'Company name is required.',
            'company_registration_number.required' => 'Company registration number is required.',
            'company_registration_number.unique' => 'This company registration number is already registered.',
            'business_type.required' => 'Business type is required.',
            'company_address.required' => 'Company address is required.',
            'company_phone.required' => 'Company phone is required.',
            'company_email.required' => 'Company email is required.',
            'company_email.email' => 'Please enter a valid email address.',
            'contact_person.required' => 'Contact person name is required.',
            'business_license_document.required' => 'Business license document is required.',
            'business_license_document.mimes' => 'Business license document must be a PDF or image file.',
        ];
    }
}
