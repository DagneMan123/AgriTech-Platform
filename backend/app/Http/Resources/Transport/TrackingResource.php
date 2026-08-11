<?php

namespace App\Http\Resources\Transport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingResource extends JsonResource
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
            'delivery_id' => $this->delivery_id,
            'tracking_number' => $this->tracking_number,
            'current_latitude' => $this->current_latitude,
            'current_longitude' => $this->current_longitude,
            'location_name' => $this->location_name,
            'status' => $this->status,
            'distance_traveled' => $this->distance_traveled,
            'estimated_time_remaining' => $this->estimated_time_remaining,
            'timestamp' => $this->timestamp,
            'created_at' => $this->created_at,
        ];
    }
}
