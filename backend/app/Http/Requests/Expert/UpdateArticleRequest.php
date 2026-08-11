<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('expert');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'article_title' => 'nullable|string|max:255',
            'article_content' => 'nullable|string|max:10000|min:100',
            'article_category' => 'nullable|string|max:255',
            'article_tags' => 'nullable|array',
            'article_tags.*' => 'string|max:100',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'article_images' => 'nullable|array',
            'article_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'article_summary' => 'nullable|string|max:500',
            'reading_time' => 'nullable|integer|min:1',
            'seo_keywords' => 'nullable|string|max:255',
            'publish_status' => 'nullable|in:draft,published,archived',
            'featured' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'article_content.min' => 'Article content must be at least 100 characters.',
            'featured_image.image' => 'Featured image must be a valid image.',
            'featured_image.max' => 'Featured image size must not exceed 2MB.',
        ];
    }
}
