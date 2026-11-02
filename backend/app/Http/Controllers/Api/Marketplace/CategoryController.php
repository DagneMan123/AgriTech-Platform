<?php

namespace App\Http\Controllers\Api\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories with real-time product counts strictly from PostgreSQL database
     */
    public function index(Request $request)
    {
        try {
            $categories = Category::where('is_active', true)
                ->orderBy('order', 'asc')
                ->orderBy('name', 'asc')
                ->get();

            $result = [];

            foreach ($categories as $cat) {
                // Product count: count active products linked to this category in database
                $count = 0;
                try {
                    $count = Product::whereIn('status', ['active', 'available', 'approved'])
                        ->where(function ($q) use ($cat) {
                            $q->where('product_category_id', $cat->id)
                              ->orWhere('category', 'ilike', $cat->name)
                              ->orWhere('category', 'ilike', $cat->slug)
                              ->orWhere('category', 'ilike', '%' . str_replace('-', '%', $cat->slug) . '%');

                            if ($cat->slug === 'cereals-grains') {
                                $q->orWhere('category', 'ilike', '%grain%')->orWhere('category', 'ilike', '%cereal%');
                            } elseif ($cat->slug === 'dairy') {
                                $q->orWhere('category', 'ilike', '%dairy%')->orWhere('category', 'ilike', '%milk%');
                            }
                        })->count();
                } catch (\Throwable $e) {
                    $count = 0;
                }

                $result[] = [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'description' => $cat->description ?: '',
                    'icon' => $cat->icon ?: 'fas fa-seedling',
                    'image' => $cat->image,
                    'is_active' => (bool)$cat->is_active,
                    'order' => (int)($cat->order ?? 0),
                    'parent_id' => $cat->parent_id,
                    'product_count' => (int)$count,
                    'products_count' => (int)$count,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            \Log::error('CategoryController@index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ], 500);
        }
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
