<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\Farmer;
use App\Http\Requests\Farmer\FarmRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FarmController extends Controller
{
    /**
     * Helper to get the farmer ID for database operations
     * Note: farms.farmer_id references users.id, NOT farmers.id
     */
    private function getFarmerRecordId($user)
    {
        return $user->id;
    }

    /**
     * Get all farms for authenticated farmer
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                Log::warning('Farm index request - no authenticated user');
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - no authenticated user',
                ], 401);
            }

            // Ensure user has farmer role
            if ($user->role !== 'farmer') {
                Log::warning('Farm index request - user is not a farmer', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied - farmer role required',
                ], 403);
            }

    // Query farms by user ID (farmer_id references users.id)
            // Select only necessary columns for better performance
            $farms = Farm::where('farmer_id', $user->id)
                ->where('deleted_at', null)
                ->select([
                    'id',
                    'name',
                    'description',
                    'address',
                    'region',
                    'zone',
                    'woreda',
                    'kebele',
                    'size_hectares',
                    'farm_type',
                    'latitude',
                    'longitude',
                ])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($farm) {
                    return [
                        'id' => $farm->id,
                        'name' => $farm->name,
                        'description' => $farm->description,
                        'address' => $farm->address,
                        'region' => $farm->region,
                        'zone' => $farm->zone,
                        'woreda' => $farm->woreda,
                        'kebele' => $farm->kebele,
                        'size_hectares' => $farm->size_hectares,
                        'farm_type' => $farm->farm_type,
                        'latitude' => $farm->latitude,
                        'longitude' => $farm->longitude,
                        'crops_count' => 0,
                    ];
                });

            Log::info('Retrieved farms for farmer', [
                'user_id' => $user->id,
                'farm_count' => $farms->count(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Farms retrieved successfully',
                'data' => $farms,
                'total' => $farms->count()
            ]);
        } catch (\Throwable $e) {
            Log::error('Error retrieving farms:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
                'exception_class' => get_class($e),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve farms. ' . ($e->getMessage() ? 'Error: ' . $e->getMessage() : 'Please try again.'),
            ], 500);
        }
    }

    /**
     * Create a new farm
     */
    public function store(FarmRequest $request)
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
            $validated['farmer_id'] = $user->id;  // Use user ID directly - farms.farmer_id references users.id
            $validated['land_type'] = $validated['land_type'] ?? 'owned';
            $validated['status'] = $validated['status'] ?? 'active';

            Log::info('Creating farm with data:', $validated);

            $farm = Farm::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Farm created successfully',
                'data' => $farm,
            ], 201);
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();

            Log::error('Error creating farm - DETAILED:', [
                'exception_class' => get_class($e),
                'message' => $errorMsg,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $request->user()?->id,
                'code' => $e->getCode(),
            ]);

            $message = 'Failed to create farm: ' . $errorMsg;
            $statusCode = 500;

            if (
                strpos($errorMsg, 'foreign key') !== false ||
                strpos($errorMsg, '23503') !== false ||
                strpos($errorMsg, 'violates') !== false
            ) {
                $message = 'Database constraint error: Cannot create farm due to foreign key mismatch.';
                $statusCode = 422;
            } elseif (strpos($errorMsg, 'duplicate') !== false) {
                $message = 'A farm with these details already exists.';
                $statusCode = 422;
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => ['farm' => [$message]]
            ], $statusCode);
        }
    }

    /**
     * Get farm details
     */
    public function show(Farm $farm)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $farm->load('crops', 'images'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving farm: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve farm',
            ], 500);
        }
    }

    /**
     * Update farm
     */
    public function update(Request $request, Farm $farm)
    {
        try {
            $user = $request->user();
            $farmerId = $this->getFarmerRecordId($user);

            if ($farm->farmer_id != $farmerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - you do not own this farm',
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'address' => 'sometimes|string',
                'region' => 'sometimes|string',
                'zone' => 'sometimes|string',
                'woreda' => 'sometimes|string',
                'kebele' => 'nullable|string',
                'size_hectares' => 'sometimes|numeric|min:0.1',
                'farm_type' => 'sometimes|in:crop,livestock,mixed,fishery',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
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
        } catch (\Exception $e) {
            Log::error('Error updating farm: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update farm: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete farm
     */
    public function destroy(Farm $farm)
    {
        try {
            $user = auth()->user();
            $farmerId = $this->getFarmerRecordId($user);

            if ($farm->farmer_id != $farmerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - you do not own this farm',
                ], 403);
            }

            if ($farm->image) {
                Storage::disk('public')->delete($farm->image);
            }

            $farm->delete();

            return response()->json([
                'success' => true,
                'message' => 'Farm deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting farm: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete farm',
            ], 500);
        }
    }

    /**
     * Get farm map data
     */
    public function mapData(Request $request)
    {
        try {
            $user = $request->user();
            $farmerId = $this->getFarmerRecordId($user);

            $farms = Farm::where('farmer_id', $farmerId)
                ->select('id', 'name', 'latitude', 'longitude', 'size_hectares')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $farms,
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving farm map data: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }
    }

    /**
     * Upload farm image
     */
    public function uploadImage(Request $request, Farm $farm)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Error uploading farm image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
            ], 500);
        }
    }

    /**
     * Get farm statistics
     */
    public function statistics(Request $request)
    {
        try {
            $user = $request->user();
            $farmerId = $this->getFarmerRecordId($user);

            $stats = [
                'total_farms' => Farm::where('farmer_id', $farmerId)->count(),
                'total_area' => Farm::where('farmer_id', $farmerId)->sum('size_hectares') ?? 0,
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
        } catch (\Exception $e) {
            Log::error('Error retrieving farm statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
            ], 500);
        }
    }
}
