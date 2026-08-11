<?php

namespace App\Http\Controllers\Api\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search
     */
    public function global(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2',
            'type' => 'sometimes|in:products,sellers,categories,all',
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $limit = $validated['limit'] ?? 10;
        $type = $validated['type'] ?? 'all';

        $results = [];

        if ($type === 'products' || $type === 'all') {
            $results['products'] = $this->searchProducts($validated['q'], $limit);
        }

        if ($type === 'sellers' || $type === 'all') {
            $results['sellers'] = $this->searchSellers($validated['q'], $limit);
        }

        if ($type === 'categories' || $type === 'all') {
            $results['categories'] = $this->searchCategories($validated['q'], $limit);
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Search products
     */
    public function products(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2',
            'category_id' => 'sometimes|exists:categories,id',
            'min_price' => 'sometimes|numeric|min:0',
            'max_price' => 'sometimes|numeric|min:0',
            'sort' => 'sometimes|in:relevance,newest,price_low,price_high,popularity,rating',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = Product::where('status', 'active')
            ->where(function ($q) use ($validated) {
                $q->where('name', 'ilike', '%' . $validated['q'] . '%')
                    ->orWhere('description', 'ilike', '%' . $validated['q'] . '%')
                    ->orWhere('tags', 'ilike', '%' . $validated['q'] . '%');
            });

        if (isset($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (isset($validated['min_price'])) {
            $query->where('price', '>=', $validated['min_price']);
        }

        if (isset($validated['max_price'])) {
            $query->where('price', '<=', $validated['max_price']);
        }

        if (isset($validated['sort'])) {
            match ($validated['sort']) {
                'newest' => $query->orderBy('created_at', 'desc'),
                'price_low' => $query->orderBy('price', 'asc'),
                'price_high' => $query->orderBy('price', 'desc'),
                'popularity' => $query->orderBy('views', 'desc'),
                'rating' => $query->orderBy('average_rating', 'desc'),
                default => $query->orderBy('created_at', 'desc'),
            };
        }

        $limit = $validated['limit'] ?? 20;
        $products = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Search sellers
     */
    private function searchSellers($query, $limit)
    {
        return \App\Models\User::where('role', 'supplier')
            ->where(function ($q) use ($query) {
                $q->where('name', 'ilike', '%' . $query . '%')
                    ->orWhere('email', 'ilike', '%' . $query . '%')
                    ->orWhere('phone', 'ilike', '%' . $query . '%');
            })
            ->with('supplier')
            ->withCount('products')
            ->limit($limit)
            ->get();
    }

    /**
     * Search categories
     */
    private function searchCategories($query, $limit)
    {
        return Category::where('type', 'product')
            ->where('status', 'active')
            ->where('name', 'ilike', '%' . $query . '%')
            ->withCount('products')
            ->limit($limit)
            ->get();
    }

    /**
     * Search products (internal method)
     */
    private function searchProducts($query, $limit)
    {
        return Product::where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'ilike', '%' . $query . '%')
                    ->orWhere('description', 'ilike', '%' . $query . '%');
            })
            ->with('category', 'supplier')
            ->limit($limit)
            ->get();
    }

    /**
     * Get search suggestions
     */
    public function suggestions(Request $request)
    {
        $validated = $request->validate([
            'q' => 'sometimes|string|min:1',
            'limit' => 'sometimes|integer|min:1|max:10',
        ]);

        $query = $validated['q'] ?? '';
        $limit = $validated['limit'] ?? 5;

        $suggestions = Product::where('status', 'active')
            ->where('name', 'ilike', '%' . $query . '%')
            ->selectRaw('DISTINCT name')
            ->limit($limit)
            ->pluck('name');

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }

    /**
     * Get advanced filters
     */
    public function filters(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
        ]);

        $filters = [
            'price_ranges' => [
                ['min' => 0, 'max' => 1000, 'label' => 'Under 1000'],
                ['min' => 1000, 'max' => 5000, 'label' => '1000 - 5000'],
                ['min' => 5000, 'max' => 10000, 'label' => '5000 - 10000'],
                ['min' => 10000, 'max' => null, 'label' => 'Above 10000'],
            ],
            'ratings' => [
                ['value' => 5, 'label' => '5 Stars'],
                ['value' => 4, 'label' => '4+ Stars'],
                ['value' => 3, 'label' => '3+ Stars'],
                ['value' => 2, 'label' => '2+ Stars'],
                ['value' => 1, 'label' => '1+ Stars'],
            ],
            'availability' => [
                ['value' => 'in_stock', 'label' => 'In Stock'],
                ['value' => 'pre_order', 'label' => 'Pre-order'],
                ['value' => 'seasonal', 'label' => 'Seasonal'],
            ],
            'condition' => [
                ['value' => 'new', 'label' => 'New'],
                ['value' => 'used', 'label' => 'Used'],
                ['value' => 'refurbished', 'label' => 'Refurbished'],
            ],
        ];

        if (isset($validated['category_id'])) {
            $filters['subcategories'] = Category::where('parent_id', $validated['category_id'])
                ->withCount('products')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $filters,
        ]);
    }

    /**
     * Get trending searches
     */
    public function trending()
    {
        // Get trending from recent searches or analytics
        $trending = [
            'rice',
            'maize',
            'wheat',
            'vegetables',
            'fertilizer',
            'seeds',
            'pesticides',
            'irrigation equipment',
        ];

        return response()->json([
            'success' => true,
            'data' => $trending,
        ]);
    }

    /**
     * Get search history (for authenticated users)
     */
    public function history(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // This would be stored in a search_history table
        $history = [];

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Clear search history
     */
    public function clearHistory()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Search history cleared',
        ]);
    }

    /**
     * Get related products
     */
    public function related(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'limit' => 'sometimes|integer|min:1|max:20',
        ]);

        $product = Product::find($validated['product_id']);
        $limit = $validated['limit'] ?? 5;

        $related = Product::where('status', 'active')
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($product) {
                $query->where('category_id', $product->category_id)
                    ->orWhere('supplier_id', $product->supplier_id);
            })
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $related,
        ]);
    }
}
