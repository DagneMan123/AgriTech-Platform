<?php

namespace App\Http\Resources\Marketplace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'category_name' => $this->category_name,
            'category_description' => $this->category_description,
            'category_icon' => $this->category_icon,
            'parent_category_id' => $this->parent_category_id,
            'parent_category_name' => $this->parentCategory?->category_name,
            'display_order' => $this->display_order,
            'is_active' => $this->is_active,
            'products_count' => $this->products_count ?? $this->products()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
