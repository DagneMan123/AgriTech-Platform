<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class FarmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['required', 'string'],
            'region' => ['required', 'string'],
            'zone' => ['required', 'string'],
            'woreda' => ['required', 'string'],
            'kebele' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'size_hectares' => ['required', 'numeric', 'min:0.1'],
            'farm_type' => ['required', 'in:crop,livestock,mixed,fishery'],
        ];
    }
}
