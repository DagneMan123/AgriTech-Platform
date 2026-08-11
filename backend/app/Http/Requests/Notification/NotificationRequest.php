<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
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
            'notification_type' => 'required|in:order,payment,delivery,consultation,training,article,loan,insurance,alert,system',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'recipient_type' => 'required|in:user,role,all',
            'recipient_user_id' => 'required_if:recipient_type,user|exists:users,id',
            'recipient_role' => 'required_if:recipient_type,role|exists:roles,name',
            'priority' => 'required|in:low,medium,high,urgent',
            'action_url' => 'nullable|url|max:255',
            'read_at' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'notification_type.required' => 'Notification type is required.',
            'title.required' => 'Notification title is required.',
            'message.required' => 'Notification message is required.',
            'recipient_type.required' => 'Recipient type is required.',
            'recipient_user_id.required_if' => 'Recipient user is required when recipient type is user.',
            'recipient_role.required_if' => 'Recipient role is required when recipient type is role.',
            'priority.required' => 'Priority is required.',
        ];
    }
}
