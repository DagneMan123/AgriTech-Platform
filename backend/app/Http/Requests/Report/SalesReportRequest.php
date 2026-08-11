<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class SalesReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('farmer') || auth()->user()->hasRole('cooperative'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'report_type' => 'required|in:daily,weekly,monthly,quarterly,yearly,custom',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'category' => 'nullable|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'farmer_id' => 'nullable|exists:users,id',
            'cooperative_id' => 'nullable|exists:cooperatives,id',
            'group_by' => 'required|in:product,category,farmer,cooperative,date',
            'export_format' => 'required|in:pdf,excel,csv,json',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'report_type.required' => 'Report type is required.',
            'start_date.required' => 'Start date is required.',
            'end_date.required' => 'End date is required.',
            'end_date.after' => 'End date must be after start date.',
            'group_by.required' => 'Group by parameter is required.',
            'export_format.required' => 'Export format is required.',
        ];
    }
}
