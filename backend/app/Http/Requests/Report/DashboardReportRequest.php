<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class DashboardReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'dashboard_type' => 'required|in:admin,farmer,buyer,supplier,cooperative,expert,financial,transport',
            'date_range' => 'required|in:today,this_week,this_month,this_quarter,this_year,custom',
            'start_date' => 'required_if:date_range,custom|date',
            'end_date' => 'required_if:date_range,custom|date|after:start_date',
            'metric_type' => 'required|array',
            'metric_type.*' => 'in:sales,orders,revenue,users,products,transactions,loans,trainings,consultations,deliveries',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'dashboard_type.required' => 'Dashboard type is required.',
            'date_range.required' => 'Date range is required.',
            'start_date.required_if' => 'Start date is required for custom date range.',
            'end_date.required_if' => 'End date is required for custom date range.',
            'end_date.after' => 'End date must be after start date.',
            'metric_type.required' => 'At least one metric type is required.',
        ];
    }
}
