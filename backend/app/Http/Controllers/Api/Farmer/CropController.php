<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\Farm;
use App\Http\Requests\Farmer\CropRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CropController extends Controller
{
    /**
     * Get all crops for farmer's farms
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            if ($user->role !== 'farmer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied - farmer role required',
                ], 403);
            }

            // Get farms for this farmer (farmer_id references users.id)
            $farms = Farm::where('farmer_id', $user->id)
                ->pluck('id')
                ->toArray();

            // If no farms exist, return empty paginated result
            if (empty($farms)) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'data' => [],
                        'current_page' => 1,
                        'per_page' => $request->get('limit', 20),
                        'total' => 0,
                        'last_page' => 1,
                    ],
                ]);
            }

            $crops = Crop::whereIn('farm_id', $farms)
                ->with('farm')
                ->paginate($request->get('limit', 20));

            return response()->json([
                'success' => true,
                'data' => $crops,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching crops: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching crops',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function store(CropRequest $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $validated = $request->validated();

            // Attempt constraint fix before operations
            try {
                \App\Services\DatabaseConstraintFixer::fixFarmsConstraint();
            } catch (\Exception $e) {
                Log::debug('Pre-crop creation constraint fix: ' . $e->getMessage());
            }

            // Verify farm exists and belongs to this user
            $farm = Farm::where('id', $validated['farm_id'])
                ->where('farmer_id', $user->id)
                ->first();

            if (!$farm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farm not found or you do not own this farm',
                ], 404);
            }

            // Remove null values for optional fields
            if (isset($validated['expected_yield_kg']) && $validated['expected_yield_kg'] === null) {
                unset($validated['expected_yield_kg']);
            }
            if (isset($validated['variety']) && $validated['variety'] === null) {
                unset($validated['variety']);
            }
            if (isset($validated['notes']) && $validated['notes'] === null) {
                unset($validated['notes']);
            }

            // Set default status if not provided
            if (!isset($validated['status']) || $validated['status'] === null) {
                $validated['status'] = 'planning';
            }

            Log::info('Creating crop with validated data', [
                'user_id' => $user->id,
                'farm_id' => $validated['farm_id'],
                'crop_type' => $validated['crop_type'] ?? null,
            ]);

            $crop = Crop::create($validated);
            $crop->load('farm');

            return response()->json([
                'success' => true,
                'message' => 'Crop created successfully',
                'data' => $crop,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error creating crop: ' . json_encode($e->errors()), [
                'user_id' => $request->user()?->id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating crop: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating crop',
                'error' => config('app.debug') ? $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine() : null,
            ], 500);
        }
    }

    /**
     * Get crop details
     */
    public function show(Crop $crop)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Check if user owns this crop
            if ($crop->farm->farmer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view this crop',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $crop->load('farm', 'growthRecords', 'harvests'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching crop: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'crop_id' => $crop->id ?? null,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching crop',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update crop
     */
    public function update(Request $request, Crop $crop)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Check if user owns this crop
            if ($crop->farm->farmer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to update this crop',
                ], 403);
            }

            $validated = $request->validate([
                'crop_type' => 'sometimes|string|max:255',
                'variety' => 'sometimes|string',
                'planting_date' => 'sometimes|date',
                'expected_harvest_date' => 'sometimes|date|after_or_equal:planting_date',
                'area_hectares' => 'sometimes|numeric|min:0.1',
                'expected_yield_kg' => 'sometimes|numeric|min:0',
                'status' => 'sometimes|in:planning,planted,growing,ready_for_harvest,harvested',
                'notes' => 'sometimes|string',
            ]);

            $crop->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Crop updated successfully',
                'data' => $crop,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating crop: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'crop_id' => $crop->id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating crop',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete crop
     */
    public function destroy(Crop $crop)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Check if user owns this crop
            if ($crop->farm->farmer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to delete this crop',
                ], 403);
            }

            $crop->delete();

            return response()->json([
                'success' => true,
                'message' => 'Crop deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting crop: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'crop_id' => $crop->id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting crop',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get crops by status
     */
    public function byStatus(Request $request, string $status)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Get farms for this farmer
            $farms = Farm::where('farmer_id', $user->id)
                ->pluck('id')
                ->toArray();

            if (empty($farms)) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'data' => [],
                        'current_page' => 1,
                        'per_page' => $request->get('limit', 20),
                        'total' => 0,
                        'last_page' => 1,
                    ],
                ]);
            }

            $crops = Crop::whereIn('farm_id', $farms)
                ->where('status', $status)
                ->with('farm')
                ->paginate($request->get('limit', 20));

            return response()->json([
                'success' => true,
                'data' => $crops,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching crops by status: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'status' => $status,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching crops',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get crop growth history
     */
    public function growthHistory(Crop $crop)
    {
        try {
            $user = auth()->user();

            if (!$user || $crop->farm->farmer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view this crop\'s history',
                ], 403);
            }

            $history = $crop->growthRecords()
                ->orderBy('recorded_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $history,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching growth history: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'crop_id' => $crop->id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching growth history',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Record crop growth
     */
    public function recordGrowth(Request $request, Crop $crop)
    {
        try {
            $user = auth()->user();

            if (!$user || $crop->farm->farmer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to record growth for this crop',
                ], 403);
            }

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
        } catch (\Exception $e) {
            Log::error('Error recording growth: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'crop_id' => $crop->id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error recording growth',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
