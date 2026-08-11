<?php

namespace App\Http\Resources\Transport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
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
            'order_id' => $this->order_id,
            'vehicle_id' => $this->vehicle_id,
            'driver_id' => $this->driver_id,
            'driver_name' => $this->driver?->name,
            'driver_phone' => $this->driver?->phone,
            'pickup_location' => $this->pickup_location,
            'pickup_date' => $this->pickup_date,
            'pickup_latitude' => $this->pickup_latitude,
            'pickup_longitude' => $this->pickup_longitude,
            'delivery_location' => $this->delivery_location,
            'delivery_date' => $this->delivery_date,
            'delivery_latitude' => $this->delivery_latitude,
            'delivery_longitude' => $this->delivery_longitude,
            'status' => $this->status,
            'special_handling' => $this->special_handling,
            'handling_instructions' => $this->handling_instructions,
            'estimated_cost' => $this->estimated_cost,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
