<?php

namespace App\Http\Controllers\Api\Market;

use App\Http\Controllers\Controller;
use App\Models\MarketPrice;
use Illuminate\Http\Request;

class MarketPriceController extends Controller
{
    /**
     * Get current commodity market prices with filtering, search & statistics
     */
    public function index(Request $request)
    {
        $query = MarketPrice::query();

        // Search query
        if ($search = $request->input('search', $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('crop_name', 'ilike', "%{$search}%")
                  ->orWhere('market_location', 'ilike', "%{$search}%")
                  ->orWhere('market_region', 'ilike', "%{$search}%")
                  ->orWhere('notes', 'ilike', "%{$search}%");
            });
        }

        // Filter by region
        if ($region = $request->input('region', $request->input('market_region'))) {
            if ($region !== 'all' && !empty($region)) {
                $query->where('market_region', 'ilike', "%{$region}%");
            }
        }

        // Filter by specific market location
        if ($location = $request->input('location', $request->input('market_location'))) {
            if ($location !== 'all' && !empty($location)) {
                $query->where('market_location', 'ilike', "%{$location}%");
            }
        }

        // Filter by trend
        if ($trend = $request->input('trend', $request->input('price_trend'))) {
            if ($trend !== 'all' && !empty($trend)) {
                $query->where('price_trend', $trend);
            }
        }

        // Sorting
        $sortBy = $request->input('sort', $request->input('sort_by', 'date_desc'));
        match ($sortBy) {
            'price_desc' => $query->orderBy('price_per_unit', 'desc'),
            'price_asc' => $query->orderBy('price_per_unit', 'asc'),
            'crop_asc' => $query->orderBy('crop_name', 'asc'),
            default => $query->orderBy('price_date', 'desc')->orderBy('id', 'desc'),
        };

        $prices = $query->get();

        // Calculate summary metrics
        $totalItems = $prices->count();
        $avgPrice = $totalItems > 0 ? (float) $prices->avg('price_per_unit') : 0;
        $increasingCount = $prices->where('price_trend', 'increasing')->count();
        $decreasingCount = $prices->where('price_trend', 'decreasing')->count();
        $stableCount = $prices->where('price_trend', 'stable')->count();

        // Distinct regions and locations for filter dropdowns
        $regions = MarketPrice::distinct()->pluck('market_region')->filter()->values();
        $locations = MarketPrice::distinct()->pluck('market_location')->filter()->values();

        return response()->json([
            'success' => true,
            'data' => $prices,
            'meta' => [
                'total' => $totalItems,
                'avg_price' => round($avgPrice, 2),
                'increasing_count' => $increasingCount,
                'decreasing_count' => $decreasingCount,
                'stable_count' => $stableCount,
                'regions' => $regions,
                'locations' => $locations,
            ]
        ]);
    }

    /**
     * Get price trends for a specific crop/category
     */
    public function trends(Request $request)
    {
        $crop = $request->input('crop', $request->input('crop_name', 'Teff'));
        $days = (int) $request->input('days', 30);
        $startDate = now()->subDays($days)->toDateString();

        $trends = MarketPrice::where('crop_name', 'ilike', "%{$crop}%")
            ->where('price_date', '>=', $startDate)
            ->orderBy('price_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'crop' => $crop,
            'data' => $trends
        ]);
    }
}
