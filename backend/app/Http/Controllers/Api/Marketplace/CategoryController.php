<?php

namespace App\Http\Controllers\Api\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories with products
     */
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        $categories = $query->with('subcategories', 'products')
            ->paginate($request->get('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get category by ID
     */
    public function show(Category $category)
    {
        return response()->json([
            'success' => true,
            'data' => $category->load('products', 'subcategories'),
        ]);
    }

    /**
     * Get products in category
     */
    public function products(Request $request, Category $category)
    {
        $products = $category->products()
            ->where('status', 'active')
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get category tree
     */
    public function tree()
    {
        $categories = Category::whereNull('parent_id')
            ->with('subcategories.subcategories')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Create category (admin only)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'sometimes|string',
            'parent_id' => 'sometimes|exists:categories,id',
            'image' => 'sometimes|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Update category (admin only)
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'parent_id' => 'sometimes|exists:categories,id',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category,
        ]);
    }

    /**
     * Delete category (admin only)
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }

    /**
     * Get popular categories
     */
    public function popular(Request $request)
    {
        $limit = $request->get('limit', 10);

        $categories = Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}
