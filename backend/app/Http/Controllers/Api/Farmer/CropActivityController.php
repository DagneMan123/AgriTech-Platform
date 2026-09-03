<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\CropActivity;
use App\Models\Crop;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CropActivityController extends Controller
{
    /**
     * Get all crop activities for the authenticated farmer
     */
    public function index(Request $request)
    {
        try {
            $farmer = Auth::user()->farmer;
            
            if (!$farmer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer profile not found'
                ], 404);
            }

            $query = CropActivity::where('farmer_id', $farmer->id)
                ->with(['crop', 'farm']);

            // Optional filters
            if ($request->has('crop_id')) {
                $query->where('crop_id', $request->crop_id);
            }

            if ($request->has('farm_id')) {
                $query->where('farm_id', $request->farm_id);
            }

            if ($request->has('activity_type')) {
                $query->where('activity_type', $request->activity_type);
            }

            if ($request->has('from_date') && $request->has('to_date')) {
                $query->whereBetween('activity_date', [
                    $request->from_date,
                    $request->to_date
                ]);
            }

            $activities = $query->orderBy('activity_date', 'desc')
                ->paginate($request->get('per_page', 50));

            return response()->json([
                'success' => true,
                'data' => $activities->items(),
                'pagination' => [
                    'total' => $activities->total(),
                    'count' => $activities->count(),
                    'per_page' => $activities->perPage(),
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching crop activities: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new crop activity
     */
    public function store(Request $request)
    {
        try {
            $farmer = Auth::user()->farmer;
            
            if (!$farmer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer profile not found'
                ], 404);
            }

            // Validate input
            $validated = $request->validate([
                'crop_id' => 'required|exists:crops,id',
                'farm_id' => 'required|exists:farms,id',
                'activity_type' => 'required|in:planting,watering,fertilizing,weeding,pesticide,pruning,harvesting,other',
                'activity_date' => 'required|date',
                'activity_time' => 'nullable|date_format:H:i',
                'description' => 'nullable|string|max:1000',
                'quantity' => 'nullable|numeric|min:0',
                'unit' => 'nullable|string|max:50',
                'cost' => 'nullable|numeric|min:0',
                'weather' => 'nullable|string|max:50',
                'notes' => 'nullable|string|max:1000'
            ]);

            // Verify the crop and farm belong to this farmer
            $crop = Crop::findOrFail($validated['crop_id']);
            $farm = Farm::findOrFail($validated['farm_id']);

            if ($crop->farm_id !== $farm->id || $farm->farmer_id !== $farmer->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Crop or farm does not belong to you'
                ], 403);
            }

            // Create activity
            $activity = CropActivity::create([
                'crop_id' => $validated['crop_id'],
                'farm_id' => $validated['farm_id'],
                'farmer_id' => $farmer->id,
                'activity_type' => $validated['activity_type'],
                'activity_date' => $validated['activity_date'],
                'activity_time' => $validated['activity_time'] ?? null,
                'description' => $validated['description'] ?? null,
                'quantity' => $validated['quantity'] ?? null,
                'unit' => $validated['unit'] ?? null,
                'cost' => $validated['cost'] ?? null,
                'weather' => $validated['weather'] ?? null,
                'notes' => $validated['notes'] ?? null
            ]);

            // Load relationships
            $activity->load(['crop', 'farm']);

            return response()->json([
                'success' => true,
                'message' => 'Crop activity created successfully',
                'data' => $activity
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating crop activity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific crop activity
     */
    public function show($id)
    {
        try {
            $farmer = Auth::user()->farmer;
            
            $activity = CropActivity::with(['crop', 'farm'])
                ->where('farmer_id', $farmer->id)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $activity
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Crop activity not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching crop activity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a crop activity
     */
    public function update(Request $request, $id)
    {
        try {
            $farmer = Auth::user()->farmer;
            
            if (!$farmer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer profile not found'
                ], 404);
            }

            $activity = CropActivity::where('farmer_id', $farmer->id)
                ->findOrFail($id);

            // Validate input
            $validated = $request->validate([
                'crop_id' => 'sometimes|required|exists:crops,id',
                'farm_id' => 'sometimes|required|exists:farms,id',
                'activity_type' => 'sometimes|required|in:planting,watering,fertilizing,weeding,pesticide,pruning,harvesting,other',
                'activity_date' => 'sometimes|required|date',
                'activity_time' => 'nullable|date_format:H:i',
                'description' => 'nullable|string|max:1000',
                'quantity' => 'nullable|numeric|min:0',
                'unit' => 'nullable|string|max:50',
                'cost' => 'nullable|numeric|min:0',
                'weather' => 'nullable|string|max:50',
                'notes' => 'nullable|string|max:1000'
            ]);

            // If crop_id or farm_id is being updated, verify ownership
            if (isset($validated['crop_id']) || isset($validated['farm_id'])) {
                $crop_id = $validated['crop_id'] ?? $activity->crop_id;
                $farm_id = $validated['farm_id'] ?? $activity->farm_id;

                $crop = Crop::findOrFail($crop_id);
                $farm = Farm::findOrFail($farm_id);

                if ($crop->farm_id !== $farm->id || $farm->farmer_id !== $farmer->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized: Crop or farm does not belong to you'
                    ], 403);
                }
            }

            // Update activity
            $activity->update($validated);
            $activity->load(['crop', 'farm']);

            return response()->json([
                'success' => true,
                'message' => 'Crop activity updated successfully',
                'data' => $activity
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Crop activity not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating crop activity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a crop activity
     */
    public function destroy($id)
    {
        try {
            $farmer = Auth::user()->farmer;
            
            $activity = CropActivity::where('farmer_id', $farmer->id)
                ->findOrFail($id);

            $activity->delete();

            return response()->json([
                'success' => true,
                'message' => 'Crop activity deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Crop activity not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting crop activity: ' . $e->getMessage()
            ], 500);
        }
    }
}
