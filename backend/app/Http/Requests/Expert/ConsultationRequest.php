<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class ConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'consultation_type' => ['required', 'in:disease_control,crop_management,soil_health,pest_management,general_farming,irrigation,fertilization,harvest_timing'],
            'images' => ['sometimes', 'array'],
            'images.*' => ['image', 'max:2048'],
        ];
    }
}
