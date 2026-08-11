<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class FinancialReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('financial'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'report_type' => 'required|in:income,expense,profit_loss,cash_flow,balance_sheet,custom',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'account_type' => 'nullable|in:loan,insurance,payment,transaction,all',
            'payment_method' => 'nullable|in:bank_transfer,mobile_money,cash,cheque,card,all',
            'status' => 'nullable|in:completed,pending,failed,all',
            'group_by' => 'required|in:date,account_type,payment_method,status',
            'include_summary' => 'nullable|boolean',
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
