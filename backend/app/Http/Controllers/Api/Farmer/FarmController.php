<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Http\Requests\Farmer\FarmRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FarmController extends Controller
{
    /**
     * Get all farms for authenticated farmer
     */
    public function index(Request $request)
    {
        $farms = Farm::where('farmer_id', Auth::id())
            ->with('crops', 'images')
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $farms,
        ]);
    }

    /**
     * Create a new farm
     */
    public function store(FarmRequest $request)
    {
        $validated = $request->validated();
        $validated['farmer_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('farms', 'public');
        }

        $farm = Farm::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Farm created successfully',
            'data' => $farm,
        ], 201);
    }

    /**
     * Get farm details
     */
    public function show(Farm $farm)
    {
        $this->authorize('view', $farm);

        return response()->json([
            'success' => true,
            'data' => $farm->load('crops', 'images', 'harvests'),
        ]);
    }

    /**
     * Update farm
     */
    public function update(Request $request, Farm $farm)
    {
        $this->authorize('update', $farm);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'location' => 'sometimes|string',
            'area_size' => 'sometimes|numeric',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'description' => 'sometimes|string',
        ]);

        if ($request->hasFile('image')) {
            if ($farm->image) {
                Storage::disk('public')->delete($farm->image);
            }
            $validated['image'] = $request->file('image')->store('farms', 'public');
        }

        $farm->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Farm updated successfully',
            'data' => $farm,
        ]);
    }

    /**
     * Delete farm
     */
    public function destroy(Farm $farm)
    {
        $this->authorize('delete', $farm);

        if ($farm->image) {
            Storage::disk('public')->delete($farm->image);
        }

        $farm->delete();

        return response()->json([
            'success' => true,
            'message' => 'Farm deleted successfully',
        ]);
    }

    /**
     * Get farm map data
     */
    public function mapData()
    {
        $farms = Farm::where('farmer_id', Auth::id())
            ->select('id', 'name', 'latitude', 'longitude', 'area_size')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $farms,
        ]);
    }

    /**
     * Upload farm image
     */
    public function uploadImage(Request $request, Farm $farm)
    {
        $this->authorize('update', $farm);

        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('farms', 'public');
            
            $farm->images()->create([
                'image_path' => $path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'image_path' => $path,
                ],
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image provided',
        ], 400);
    }

    /**
     * Get farm statistics
     */
    public function statistics()
    {
        $farmerId = Auth::id();

        $stats = [
            'total_farms' => Farm::where('farmer_id', $farmerId)->count(),
            'total_area' => Farm::where('farmer_id', $farmerId)->sum('area_size'),
            'active_crops' => \App\Models\Crop::whereHas('farm', function ($q) use ($farmerId) {
                $q->where('farmer_id', $farmerId);
            })->where('status', 'active')->count(),
            'total_harvests' => \App\Models\Harvest::whereHas('crop.farm', function ($q) use ($farmerId) {
                $q->where('farmer_id', $farmerId);
            })->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
