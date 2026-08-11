<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\Farm;
use App\Http\Requests\Farmer\CropRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CropController extends Controller
{
    /**
     * Get all crops for farmer's farms
     */
    public function index(Request $request)
    {
        $farmerId = Auth::id();

        $crops = Crop::whereHas('farm', function ($q) use ($farmerId) {
            $q->where('farmer_id', $farmerId);
        })
        ->with('farm', 'growthRecords')
        ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $crops,
        ]);
    }

    /**
     * Create a new crop
     */
    public function store(CropRequest $request)
    {
        $validated = $request->validated();
        $farmerId = Auth::id();

        // Verify farm ownership
        $farm = Farm::where('id', $validated['farm_id'])
            ->where('farmer_id', $farmerId)
            ->firstOrFail();

        $crop = Crop::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Crop created successfully',
            'data' => $crop,
        ], 201);
    }

    /**
     * Get crop details
     */
    public function show(Crop $crop)
    {
        $this->authorize('view', $crop);

        return response()->json([
            'success' => true,
            'data' => $crop->load('farm', 'growthRecords', 'harvests'),
        ]);
    }

    /**
     * Update crop
     */
    public function update(Request $request, Crop $crop)
    {
        $this->authorize('update', $crop);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'variety' => 'sometimes|string',
            'planting_date' => 'sometimes|date',
            'expected_harvest_date' => 'sometimes|date|after:planting_date',
            'quantity_planted' => 'sometimes|numeric',
            'status' => 'sometimes|in:planning,planted,growing,ready_for_harvest,harvested',
            'notes' => 'sometimes|string',
        ]);

        $crop->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Crop updated successfully',
            'data' => $crop,
        ]);
    }

    /**
     * Delete crop
     */
    public function destroy(Crop $crop)
    {
        $this->authorize('delete', $crop);

        $crop->delete();

        return response()->json([
            'success' => true,
            'message' => 'Crop deleted successfully',
        ]);
    }

    /**
     * Get crops by status
     */
    public function byStatus(Request $request, string $status)
    {
        $farmerId = Auth::id();

        $crops = Crop::whereHas('farm', function ($q) use ($farmerId) {
            $q->where('farmer_id', $farmerId);
        })
        ->where('status', $status)
        ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $crops,
        ]);
    }

    /**
     * Get crop growth history
     */
    public function growthHistory(Crop $crop)
    {
        $this->authorize('view', $crop);

        $history = $crop->growthRecords()
            ->orderBy('recorded_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Record crop growth
     */
    public function recordGrowth(Request $request, Crop $crop)
    {
        $this->authorize('update', $crop);

        $validated = $request->validate([
            'height' => 'required|numeric',
            'stage' => 'required|string',
            'health_status' => 'required|in:good,fair,poor',
            'notes' => 'sometimes|string',
            'recorded_date' => 'sometimes|date',
        ]);

        $validated['crop_id'] = $crop->id;
        $validated['recorded_date'] = $validated['recorded_date'] ?? now();

        $growthRecord = \App\Models\CropGrowthRecord::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Growth record created successfully',
            'data' => $growthRecord,
        ], 201);
    }
}
