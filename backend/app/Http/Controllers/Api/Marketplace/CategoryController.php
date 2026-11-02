<?php

namespace App\Http\Controllers\Api\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories with real-time product counts from PostgreSQL database
     */
    public function index(Request $request)
    {
        try {
            $categories = Category::where('is_active', true)
                ->orderBy('order', 'asc')
                ->orderBy('name', 'asc')
                ->get();

            // Auto-seed if categories table is empty
            if ($categories->isEmpty()) {
                try {
                    \Artisan::call('db:seed', ['--class' => 'CategorySeeder', '--force' => true]);
                    $categories = Category::where('is_active', true)
                        ->orderBy('order', 'asc')
                        ->orderBy('name', 'asc')
                        ->get();
                } catch (\Throwable $e) {
                    // Ignore seeder exceptions
                }
            }

            // Collect distinct categories directly from products table (crops/produce posted by farmers)
            $productCategoryCounts = collect();
            try {
                $productCategoryCounts = Product::whereIn('status', ['active', 'available', 'approved'])
                    ->whereNotNull('category')
                    ->where('category', '!=', '')
                    ->selectRaw('category, count(*) as count')
                    ->groupBy('category')
                    ->pluck('count', 'category');
            } catch (\Throwable $e) {
                // Ignore if query fails
            }

            $result = [];
            $seen = [];

            foreach ($categories as $cat) {
                $catName = $cat->name;
                $seen[strtolower($catName)] = true;
                $seen[strtolower($cat->slug)] = true;

                // Product count: count via relationship OR matching string in products.category
                $count = 0;
                try {
                    $count = Product::whereIn('status', ['active', 'available', 'approved'])
                        ->where(function ($q) use ($cat) {
                            $q->where('category_id', $cat->id)
                              ->orWhere('category', 'ilike', $cat->name)
                              ->orWhere('category', 'ilike', "%{$cat->slug}%");
                        })->count();
                } catch (\Throwable $e) {
                    $count = 0;
                }

                $result[] = [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'description' => $cat->description ?: "Fresh agricultural {$cat->name} supplied directly from Ethiopian farmers and producers.",
                    'icon' => $cat->icon ?: 'fas fa-seedling',
                    'image' => $cat->image ?? null,
                    'is_active' => true,
                    'order' => $cat->order ?? 1,
                    'product_count' => $count,
                    'products_count' => $count,
                ];
            }

            // Add any categories from products table that aren't in Category table yet
            foreach ($productCategoryCounts as $pCat => $count) {
                $lower = strtolower($pCat);
                if (!isset($seen[$lower])) {
                    $seen[$lower] = true;
                    $result[] = [
                        'id' => count($result) + 1,
                        'name' => ucfirst($pCat),
                        'slug' => \Illuminate\Support\Str::slug($pCat),
                        'description' => "Fresh agricultural " . ucfirst($pCat) . " directly from local producers.",
                        'icon' => 'fas fa-leaf',
                        'image' => null,
                        'is_active' => true,
                        'order' => count($result) + 1,
                        'product_count' => (int)$count,
                        'products_count' => (int)$count,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            \Log::error('CategoryController@index error: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => $e->getMessage()
            ]);
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
