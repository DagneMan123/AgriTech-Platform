<?php

namespace App\Http\Resources\Marketplace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductReviewResource extends JsonResource
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
            'product_id' => $this->product_id,
            'buyer_id' => $this->buyer_id,
            'buyer_name' => $this->buyer?->name,
            'rating' => $this->rating,
            'title' => $this->title,
            'comment' => $this->comment,
            'review_images' => $this->review_images,
            'helpful_count' => $this->helpful_count ?? 0,
            'unhelpful_count' => $this->unhelpful_count ?? 0,
            'verified_purchase' => $this->verified_purchase,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
