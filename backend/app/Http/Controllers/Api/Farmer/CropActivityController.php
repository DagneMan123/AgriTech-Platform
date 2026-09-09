<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Farmer\CropActivityResource;
use App\Models\CropActivity;
use App\Models\Crop;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CropActivityController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role !== 'farmer') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Get or create the farmer record for this user
            $farmer = $this->getOrCreateFarmer();
            
            if (!$farmer) {
                return response()->json(['success' => true, 'data' => [], 'total' => 0], 200);
            }

            try {
                $activities = CropActivity::where('farmer_id', $farmer->id)
                    ->with(['crop', 'farm'])
                    ->orderBy('activity_date', 'desc')
                    ->get();
                
                return response()->json([
                    'success' => true,
                    'data' => CropActivityResource::collection($activities),
                    'total' => $activities->count()
                ], 200);
            } catch (\Exception $e) {
                Log::debug('Table check: ' . $e->getMessage());
                return response()->json(['success' => true, 'data' => [], 'total' => 0], 200);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching crop activities: ' . $e->getMessage());
            return response()->json(['success' => true, 'data' => [], 'total' => 0], 200);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role !== 'farmer') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Get or create the farmer record for this user
            $farmer = $this->getOrCreateFarmer();
            
            if (!$farmer) {
                return response()->json(['success' => false, 'message' => 'Unable to create farmer profile'], 500);
            }

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

            // Verify ownership - check that crop and farm belong to this user
            $crop = Crop::findOrFail($validated['crop_id']);
            $farm = Farm::findOrFail($validated['farm_id']);

            // The farms table stores user_id in farmer_id column, not farmer table id
            if ($crop->farm_id !== $farm->id || $farm->farmer_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'You do not have permission to perform this action on this crop or farm.'], 403);
            }

            // Create activity with the farmer's id from farmers table
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

            $activity->load(['crop', 'farm']);

            return response()->json([
                'success' => true,
                'message' => 'Activity created successfully',
                'data' => new CropActivityResource($activity)
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating crop activity: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            
            // Get or create the farmer record for this user
            $farmer = $this->getOrCreateFarmer();
            
            if (!$farmer) {
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }
            
            $activity = CropActivity::with(['crop', 'farm'])
                ->where('farmer_id', $farmer->id)
                ->findOrFail($id);

            return response()->json(['success' => true, 'data' => new CropActivityResource($activity)], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            // Get or create the farmer record for this user
            $farmer = $this->getOrCreateFarmer();
            
            if (!$farmer) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            
            $activity = CropActivity::where('farmer_id', $farmer->id)->findOrFail($id);

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

            if (isset($validated['crop_id']) || isset($validated['farm_id'])) {
                $crop_id = $validated['crop_id'] ?? $activity->crop_id;
                $farm_id = $validated['farm_id'] ?? $activity->farm_id;
                $crop = Crop::findOrFail($crop_id);
                $farm = Farm::findOrFail($farm_id);
                
               
                if ($crop->farm_id !== $farm->id || $farm->farmer_id !== $user->id) {
                    return response()->json(['success' => false, 'message' => 'You do not have permission to perform this action on this crop or farm.'], 403);
                }
            }

            $activity->update($validated);
            $activity->load(['crop', 'farm']);

            return response()->json([
                'success' => true,
                'message' => 'Activity updated successfully',
                'data' => new CropActivityResource($activity)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating activity: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            
            // Get or create the farmer record for this user
            $farmer = $this->getOrCreateFarmer();
            
            if (!$farmer) {
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }
            
            $activity = CropActivity::where('farmer_id', $farmer->id)->findOrFail($id);
            $activity->delete();

            return response()->json(['success' => true, 'message' => 'Activity deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
    }
}
