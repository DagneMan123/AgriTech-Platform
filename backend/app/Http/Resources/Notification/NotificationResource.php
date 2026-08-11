<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'notification_type' => $this->notification_type,
            'title' => $this->title,
            'message' => $this->message,
            'recipient_type' => $this->recipient_type,
            'recipient_user_id' => $this->recipient_user_id,
            'recipient_role' => $this->recipient_role,
            'priority' => $this->priority,
            'action_url' => $this->action_url,
            'is_read' => $this->is_read,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }
}
