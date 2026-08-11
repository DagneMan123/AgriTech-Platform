<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('farmer');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'expert_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'consultation_type' => 'required|in:crop,soil,pest,irrigation,fertilizer,general',
            'priority' => 'required|in:low,medium,high,urgent',
            'budget' => 'nullable|numeric|min:0',
            'preferred_date' => 'nullable|date|after_or_equal:today',
            'attachments' => 'nullable|array',
            'attachments.*' => 'mimes:jpeg,png,jpg,gif,pdf,doc,docx|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'expert_id.required' => 'Expert selection is required.',
            'expert_id.exists' => 'Selected expert does not exist.',
            'title.required' => 'Consultation title is required.',
            'description.required' => 'Consultation description is required.',
            'consultation_type.required' => 'Consultation type is required.',
            'priority.required' => 'Priority level is required.',
        ];
    }
}
