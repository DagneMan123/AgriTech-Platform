<?php

namespace App\Http\Resources\Transport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteResource extends JsonResource
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
            'route_name' => $this->route_name,
            'waypoints' => $this->waypoints,
            'total_distance' => $this->total_distance,
            'estimated_time' => $this->estimated_time,
            'actual_time' => $this->actual_time,
            'optimal_route' => $this->optimal_route,
            'route_status' => $this->route_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
