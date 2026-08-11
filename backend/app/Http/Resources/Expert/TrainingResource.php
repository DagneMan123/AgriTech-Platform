<?php

namespace App\Http\Resources\Expert;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingResource extends JsonResource
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
            'expert_id' => $this->expert_id,
            'expert_name' => $this->expert?->name,
            'training_title' => $this->training_title,
            'training_description' => $this->training_description,
            'training_category' => $this->training_category,
            'training_level' => $this->training_level,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'max_participants' => $this->max_participants,
            'current_participants' => $this->current_participants ?? $this->participants()->count(),
            'training_fee' => $this->training_fee,
            'training_language' => $this->training_language,
            'training_image' => $this->training_image,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
