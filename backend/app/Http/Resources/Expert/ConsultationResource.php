<?php

namespace App\Http\Resources\Expert;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationResource extends JsonResource
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
            'farmer_id' => $this->farmer_id,
            'farmer_name' => $this->farmer?->name,
            'expert_id' => $this->expert_id,
            'expert_name' => $this->expert?->name,
            'title' => $this->title,
            'description' => $this->description,
            'consultation_type' => $this->consultation_type,
            'priority' => $this->priority,
            'status' => $this->status,
            'budget' => $this->budget,
            'preferred_date' => $this->preferred_date,
            'replies_count' => $this->replies_count ?? $this->replies()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
