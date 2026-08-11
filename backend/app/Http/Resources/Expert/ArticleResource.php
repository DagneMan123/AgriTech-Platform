<?php

namespace App\Http\Resources\Expert;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
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
            'article_title' => $this->article_title,
            'article_content' => $this->article_content,
            'article_category' => $this->article_category,
            'article_tags' => $this->article_tags,
            'featured_image' => $this->featured_image,
            'article_images' => $this->article_images,
            'article_summary' => $this->article_summary,
            'reading_time' => $this->reading_time,
            'seo_keywords' => $this->seo_keywords,
            'publish_status' => $this->publish_status,
            'featured' => $this->featured,
            'views_count' => $this->views_count ?? 0,
            'likes_count' => $this->likes_count ?? $this->likes()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
