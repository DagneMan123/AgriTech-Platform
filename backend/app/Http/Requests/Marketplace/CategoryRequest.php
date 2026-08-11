<?php

namespace App\Http\Requests\Marketplace;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_name' => 'required|string|max:255|unique:categories',
            'category_description' => 'nullable|string|max:1000',
            'category_icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'parent_category_id' => 'nullable|exists:categories,id',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_name.required' => 'Category name is required.',
            'category_name.unique' => 'This category name already exists.',
            'category_icon.image' => 'Category icon must be a valid image.',
            'category_icon.max' => 'Category icon size must not exceed 2MB.',
            'parent_category_id.exists' => 'Selected parent category does not exist.',
        ];
    }
}
