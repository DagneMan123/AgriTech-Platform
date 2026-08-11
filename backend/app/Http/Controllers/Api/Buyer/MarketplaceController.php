<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    /**
     * Get products with filters and search
     */
    public function index(Request $request)
    {
        $query = Product::where('status', 'active');

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by price range
        if ($request->has('min_price') && $request->has('max_price')) {
            $query->whereBetween('price', [
                $request->min_price,
                $request->max_price,
            ]);
        }

        // Filter by seller
        if ($request->has('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->withCount('reviews')->orderBy('reviews_count', 'desc');
                break;
            default:
                $query->latest();
        }

        $products = $query->with('images', 'reviews')
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get product details
     */
    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => $product->load('images', 'reviews', 'seller'),
        ]);
    }

    /**
     * Get featured products
     */
    public function featured()
    {
        $products = Product::where('status', 'active')
            ->where('featured', true)
            ->latest()
            ->limit(12)
            ->with('images')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get trending products
     */
    public function trending()
    {
        $products = Product::where('status', 'active')
            ->withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit(12)
            ->with('images')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get products by category
     */
    public function byCategory(Request $request, string $category)
    {
        $products = Product::where('status', 'active')
            ->where('category', $category)
            ->with('images', 'reviews')
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get similar products
     */
    public function similar(Product $product)
    {
        $similar = Product::where('status', 'active')
            ->where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->limit(6)
            ->with('images')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $similar,
        ]);
    }

    /**
     * Compare products
     */
    public function compare(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:2|max:4',
            'product_ids.*' => 'exists:products,id',
        ]);

        $products = Product::whereIn('id', $validated['product_ids'])
            ->with('images', 'reviews')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get categories
     */
    public function categories()
    {
        $categories = Category::whereNull('parent_id')
            ->with('subcategories')
            ->withCount('products')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get price range
     */
    public function priceRange()
    {
        $minPrice = Product::where('status', 'active')->min('price');
        $maxPrice = Product::where('status', 'active')->max('price');

        return response()->json([
            'success' => true,
            'data' => [
                'min' => $minPrice ?? 0,
                'max' => $maxPrice ?? 0,
            ],
        ]);
    }
}
