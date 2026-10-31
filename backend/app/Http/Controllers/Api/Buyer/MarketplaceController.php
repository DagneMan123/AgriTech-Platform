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
        $query = Product::whereIn('status', ['active', 'available']);

        // Search
        if ($request->filled('search') || $request->filled('q')) {
            $search = $request->search ?? $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%$search%")
                    ->orWhere('description', 'ilike', "%$search%")
                    ->orWhere('category', 'ilike', "%$search%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $category = $request->category;
            $query->where(function ($q) use ($category) {
                $q->where('category', 'ilike', $category)
                    ->orWhere('category', 'ilike', "%$category%");
            });
        }

        // Filter by price range
        if ($request->filled('min_price') && $request->filled('max_price')) {
            $query->whereBetween('price', [
                (float) $request->min_price,
                (float) $request->max_price,
            ]);
        } elseif ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        } elseif ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        // Filter by seller/farmer
        if ($request->filled('farmer_id')) {
            $query->where('farmer_id', $request->farmer_id);
        } elseif ($request->filled('seller_id')) {
            $query->where('farmer_id', $request->seller_id);
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_low':
            case 'price-low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
            case 'price-high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;
            default:
                $query->latest();
        }

        $products = $query->with(['farmer.user', 'reviews'])
            ->paginate($request->get('limit', 24));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Get product details
     */
    public function show($id)
    {
        $product = Product::with(['farmer.user', 'reviews'])->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Get featured products
     */
    public function featured()
    {
        $products = Product::whereIn('status', ['active', 'available'])
            ->orderBy('views_count', 'desc')
            ->limit(12)
            ->with(['farmer.user'])
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
        $products = Product::whereIn('status', ['active', 'available'])
            ->orderBy('views_count', 'desc')
            ->limit(12)
            ->with(['farmer.user'])
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
        $products = Product::whereIn('status', ['active', 'available'])
            ->where('category', 'ilike', "%$category%")
            ->with(['farmer.user', 'reviews'])
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get similar products
     */
    public function similar($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $similar = Product::whereIn('status', ['active', 'available'])
            ->where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->limit(6)
            ->with(['farmer.user'])
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
            ->with(['farmer.user', 'reviews'])
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
        $distinctCategories = Product::whereIn('status', ['active', 'available'])
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        $categories = $distinctCategories->map(function ($cat, $index) {
            return [
                'id' => $index + 1,
                'name' => ucfirst($cat),
                'slug' => strtolower(str_replace(' ', '-', $cat)),
                'products_count' => Product::whereIn('status', ['active', 'available'])->where('category', $cat)->count(),
            ];
        });

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
        $minPrice = Product::whereIn('status', ['active', 'available'])->min('price');
        $maxPrice = Product::whereIn('status', ['active', 'available'])->max('price');

        return response()->json([
            'success' => true,
            'data' => [
                'min' => (float)($minPrice ?? 0),
                'max' => (float)($maxPrice ?? 0),
            ],
        ]);
    }
}
