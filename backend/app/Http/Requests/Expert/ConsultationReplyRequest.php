<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class ConsultationReplyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('expert');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'consultation_id' => 'required|exists:consultations,id',
            'reply_text' => 'required|string|max:5000|min:20',
            'recommendations' => 'nullable|array',
            'recommendations.*' => 'string|max:500',
            'attachments' => 'nullable|array',
            'attachments.*' => 'mimes:pdf,doc,docx,jpg,jpeg,png,gif|max:2048',
            'follow_up_date' => 'nullable|date|after:today',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'consultation_id.required' => 'Consultation is required.',
            'consultation_id.exists' => 'Selected consultation does not exist.',
            'reply_text.required' => 'Reply text is required.',
            'reply_text.min' => 'Reply text must be at least 20 characters.',
            'reply_text.max' => 'Reply text cannot exceed 5000 characters.',
        ];
    }
}
