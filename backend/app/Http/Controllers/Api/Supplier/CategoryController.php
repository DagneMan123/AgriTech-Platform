<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Get all categories
     */
    public function index()
    {
        $categories = Category::where('type', 'supply')
            ->where('status', 'active')
            ->with('subcategories')
            ->orderBy('name', 'asc')
            ->get();

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
        if ($category->type !== 'supply') {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $category->load(['subcategories', 'products']);

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * Get categories with product count
     */
    public function withCounts()
    {
        $categories = Category::where('type', 'supply')
            ->where('status', 'active')
            ->withCount('products')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get products by category for supplier
     */
    public function getProducts(Category $category)
    {
        if ($category->type !== 'supply') {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $products = $category->products()
            ->where('supplier_id', Auth::id())
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get popular categories
     */
    public function popular()
    {
        $categories = Category::where('type', 'supply')
            ->where('status', 'active')
            ->withCount('products')
            ->orderByDesc('products_count')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Search categories
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $categories = Category::where('type', 'supply')
            ->where('status', 'active')
            ->where('name', 'ilike', '%' . $validated['q'] . '%')
            ->with('subcategories')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}
