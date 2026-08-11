<?php

namespace App\Http\Controllers\Api\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all products for marketplace
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'sort' => 'sometimes|in:newest,price_low,price_high,rating,popularity',
            'filter_in_stock' => 'sometimes|boolean',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = Product::where('status', 'active')
            ->with('category', 'supplier');

        if (isset($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if ($validated['filter_in_stock'] ?? false) {
            $query->where('stock_quantity', '>', 0);
        }

        if (isset($validated['sort'])) {
            match ($validated['sort']) {
                'newest' => $query->orderBy('created_at', 'desc'),
                'price_low' => $query->orderBy('price', 'asc'),
                'price_high' => $query->orderBy('price', 'desc'),
                'rating' => $query->orderBy('average_rating', 'desc'),
                'popularity' => $query->orderBy('views', 'desc'),
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
     * Get product details
     */
    public function show(Product $product)
    {
        if ($product->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $product->load('category', 'supplier', 'reviews');
        $product->increment('views');

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
        $products = Product::where('status', 'active')
            ->where('is_featured', true)
            ->with('category', 'supplier')
            ->orderBy('featured_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get on-sale products
     */
    public function onSale(Request $request)
    {
        $validated = $request->validate([
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $limit = $validated['limit'] ?? 20;

        $products = Product::where('status', 'active')
            ->where('discount_percentage', '>', 0)
            ->with('category', 'supplier')
            ->orderBy('discount_percentage', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get new arrivals
     */
    public function newArrivals(Request $request)
    {
        $validated = $request->validate([
            'days' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $days = $validated['days'] ?? 30;
        $limit = $validated['limit'] ?? 20;

        $products = Product::where('status', 'active')
            ->where('created_at', '>=', now()->subDays($days))
            ->with('category', 'supplier')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get best sellers
     */
    public function bestSellers(Request $request)
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:week,month,quarter,year',
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $period = $validated['period'] ?? 'month';
        $limit = $validated['limit'] ?? 20;

        $startDate = match ($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
        };

        $products = Product::where('status', 'active')
            ->where('created_at', '>=', $startDate)
            ->withCount('orders')
            ->with('category', 'supplier')
            ->orderBy('orders_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get recommended products
     */
    public function recommended(Request $request)
    {
        $validated = $request->validate([
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $limit = $validated['limit'] ?? 10;

        // Get products with high ratings and recent sales
        $products = Product::where('status', 'active')
            ->where('average_rating', '>=', 4)
            ->with('category', 'supplier')
            ->orderBy('average_rating', 'desc')
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get products by category
     */
    public function byCategory(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        $limit = $validated['limit'] ?? 20;

        $products = Product::where('status', 'active')
            ->where('category_id', $validated['category_id'])
            ->with('supplier')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get products by supplier
     */
    public function bySupplier(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:users,id',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        $limit = $validated['limit'] ?? 20;

        $products = Product::where('status', 'active')
            ->where('supplier_id', $validated['supplier_id'])
            ->with('category')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get product reviews
     */
    public function reviews(Product $product)
    {
        $reviews = $product->reviews()
            ->where('status', 'approved')
            ->with('buyer')
            ->orderBy('rating', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Get product availability
     */
    public function availability(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::find($validated['product_id']);

        $availability = [
            'in_stock' => $product->stock_quantity > 0,
            'stock_quantity' => $product->stock_quantity,
            'status' => $product->stock_quantity > 10 ? 'available' : 'limited',
            'expected_restock' => $product->expected_restock_date,
            'can_pre_order' => $product->allow_pre_order,
        ];

        return response()->json([
            'success' => true,
            'data' => $availability,
        ]);
    }

    /**
     * Get product comparison
     */
    public function compare(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:2|max:4',
            'product_ids.*' => 'exists:products,id',
        ]);

        $products = Product::whereIn('id', $validated['product_ids'])
            ->where('status', 'active')
            ->with('category', 'supplier')
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
            ->orderBy('views', 'desc')
            ->with('category', 'supplier')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get seasonal products
     */
    public function seasonal()
    {
        $products = Product::where('status', 'active')
            ->where('is_seasonal', true)
            ->with('category', 'supplier')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get organic products
     */
    public function organic()
    {
        $products = Product::where('status', 'active')
            ->where('is_organic', true)
            ->with('category', 'supplier')
            ->orderBy('average_rating', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }
}
