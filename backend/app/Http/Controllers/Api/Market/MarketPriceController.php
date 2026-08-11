<?php

namespace App\Http\Controllers\Api\Market;

use App\Http\Controllers\Controller;
use App\Models\MarketPrice;
use App\Models\Product;
use Illuminate\Http\Request;

class MarketPriceController extends Controller
{
    /**
     * Get current market prices
     */
    public function index(Request $request)
    {
        $query = MarketPrice::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $prices = $query->latest('recorded_at')
            ->paginate($request->get('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $prices,
        ]);
    }

    /**
     * Get price trends
     */
    public function trends(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'days' => 'sometimes|integer|min:1|max:90',
        ]);

        $days = $validated['days'] ?? 30;
        $startDate = now()->subDays($days);

        $trends = MarketPrice::where('category', $validated['category'])
            ->where('recorded_at', '>=', $startDate)
            ->orderBy('recorded_at')
            ->get()
            ->groupBy(function ($price) {
                return $price->recorded_at->format('Y-m-d');
            })
            ->map(function ($group) {
                return [
                    'date' => $group->first()->recorded_at->format('Y-m-d'),
                    'average_price' => $group->avg('price'),
                    'min_price' => $group->min('price'),
                    'max_price' => $group->max('price'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $trends->values(),
        ]);
    }

    /**
     * Get price comparison
     */
    public function comparison(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array',
            'products.*' => 'exists:products,id',
        ]);

        $prices = Product::whereIn('id', $validated['products'])
            ->with('prices' => function ($q) {
                $q->latest()->limit(1);
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $prices,
        ]);
    }

    /**
     * Get price forecast
     */
    public function forecast(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'days' => 'sometimes|integer|min:1|max:30',
        ]);

        $days = $validated['days'] ?? 7;

        // Get historical prices
        $prices = MarketPrice::where('category', $validated['category'])
            ->where('recorded_at', '>=', now()->subMonths(3))
            ->orderBy('recorded_at')
            ->get();

        // Simple forecast using average
        $avgPrice = $prices->avg('price');
        $forecast = [];

        for ($i = 1; $i <= $days; $i++) {
            $forecast[] = [
                'date' => now()->addDays($i)->format('Y-m-d'),
                'forecast_price' => $avgPrice + (rand(-10, 10)),
                'confidence' => rand(70, 95),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $forecast,
        ]);
    }

    /**
     * Record market price (admin only)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'category' => 'required|string',
            'price' => 'required|numeric',
            'region' => 'sometimes|string',
            'source' => 'sometimes|string',
        ]);

        $marketPrice = MarketPrice::create([
            ...$validated,
            'recorded_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Market price recorded successfully',
            'data' => $marketPrice,
        ], 201);
    }

    /**
     * Get price by category
     */
    public function byCategory(string $category)
    {
        $prices = MarketPrice::where('category', $category)
            ->latest('recorded_at')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $prices,
        ]);
    }
}
