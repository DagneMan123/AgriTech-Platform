<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingRequest extends FormRequest
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
            'training_title' => 'required|string|max:255',
            'training_description' => 'required|string|max:2000',
            'training_category' => 'required|string|max:255',
            'training_level' => 'required|in:beginner,intermediate,advanced',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'max_participants' => 'required|integer|min:1',
            'training_fee' => 'nullable|numeric|min:0',
            'training_language' => 'nullable|string|max:100',
            'training_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'training_materials' => 'nullable|array',
            'training_materials.*' => 'mimes:pdf,doc,docx,xls,xlsx|max:5120',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'training_title.required' => 'Training title is required.',
            'training_description.required' => 'Training description is required.',
            'training_category.required' => 'Training category is required.',
            'training_level.required' => 'Training level is required.',
            'start_date.required' => 'Start date is required.',
            'end_date.required' => 'End date is required.',
            'end_date.after' => 'End date must be after start date.',
            'location.required' => 'Training location is required.',
            'max_participants.required' => 'Maximum participants is required.',
            'training_image.image' => 'Training image must be a valid image.',
        ];
    }
}
