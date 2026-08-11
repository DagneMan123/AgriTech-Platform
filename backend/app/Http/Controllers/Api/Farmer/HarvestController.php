<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Harvest;
use App\Models\Crop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HarvestController extends Controller
{
    /**
     * Get all harvests for farmer
     */
    public function index(Request $request)
    {
        $farmerId = Auth::id();

        $harvests = Harvest::whereHas('crop.farm', function ($q) use ($farmerId) {
            $q->where('farmer_id', $farmerId);
        })
        ->with('crop.farm')
        ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $harvests,
        ]);
    }

    /**
     * Create harvest record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'crop_id' => 'required|exists:crops,id',
            'harvest_date' => 'required|date',
            'quantity' => 'required|numeric',
            'unit' => 'required|string',
            'quality_grade' => 'sometimes|in:excellent,good,fair,poor',
            'notes' => 'sometimes|string',
        ]);

        // Verify crop ownership
        $crop = Crop::findOrFail($validated['crop_id']);
        $this->authorize('update', $crop);

        $harvest = Harvest::create($validated);

        // Update crop status
        $crop->update(['status' => 'harvested']);

        return response()->json([
            'success' => true,
            'message' => 'Harvest record created successfully',
            'data' => $harvest,
        ], 201);
    }

    /**
     * Get harvest details
     */
    public function show(Harvest $harvest)
    {
        $this->authorize('view', $harvest);

        return response()->json([
            'success' => true,
            'data' => $harvest->load('crop'),
        ]);
    }

    /**
     * Update harvest
     */
    public function update(Request $request, Harvest $harvest)
    {
        $this->authorize('update', $harvest);

        $validated = $request->validate([
            'harvest_date' => 'sometimes|date',
            'quantity' => 'sometimes|numeric',
            'unit' => 'sometimes|string',
            'quality_grade' => 'sometimes|in:excellent,good,fair,poor',
            'notes' => 'sometimes|string',
        ]);

        $harvest->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Harvest updated successfully',
            'data' => $harvest,
        ]);
    }

    /**
     * Delete harvest
     */
    public function destroy(Harvest $harvest)
    {
        $this->authorize('delete', $harvest);

        $harvest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Harvest deleted successfully',
        ]);
    }

    /**
     * Get harvest statistics
     */
    public function statistics(Request $request)
    {
        $farmerId = Auth::id();
        $year = $request->get('year', now()->year);

        $harvests = Harvest::whereHas('crop.farm', function ($q) use ($farmerId) {
            $q->where('farmer_id', $farmerId);
        })
        ->whereYear('harvest_date', $year)
        ->get();

        $stats = [
            'total_harvests' => $harvests->count(),
            'total_quantity' => $harvests->sum('quantity'),
            'average_quantity' => $harvests->count() > 0 ? $harvests->sum('quantity') / $harvests->count() : 0,
            'by_quality' => $harvests->groupBy('quality_grade')->map->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
