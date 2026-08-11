<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrainingRequest extends FormRequest
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
            'training_title' => 'nullable|string|max:255',
            'training_description' => 'nullable|string|max:2000',
            'training_category' => 'nullable|string|max:255',
            'training_level' => 'nullable|in:beginner,intermediate,advanced',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'location' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'max_participants' => 'nullable|integer|min:1',
            'training_fee' => 'nullable|numeric|min:0',
            'training_language' => 'nullable|string|max:100',
            'training_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'training_materials' => 'nullable|array',
            'training_materials.*' => 'mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'status' => 'nullable|in:planning,scheduled,ongoing,completed,cancelled',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'end_date.after' => 'End date must be after start date.',
            'training_image.image' => 'Training image must be a valid image.',
        ];
    }
}
