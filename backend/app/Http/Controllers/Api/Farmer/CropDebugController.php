<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CropDebugController extends Controller
{
    /**
     * Debug endpoint to test crop creation with detailed output
     */
    public function debugCreate(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated',
                    'debug' => ['user' => null],
                ], 401);
            }

            Log::info('Debug Create Crop - User authenticated', [
                'user_id' => $user->id,
                'user_role' => $user->role,
            ]);

            // Log raw request data
            Log::info('Debug Create Crop - Raw request', [
                'body' => $request->all(),
            ]);

            // Validate
            $validated = $request->validate([
                'farm_id' => ['required', 'integer', 'exists:farms,id'],
                'crop_type' => ['required', 'string', 'max:255'],
                'variety' => ['nullable', 'string', 'max:255'],
                'planting_date' => ['required', 'date_format:Y-m-d'],
                'expected_harvest_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:planting_date'],
                'area_hectares' => ['required', 'numeric', 'min:0.1', 'max:99999.99'],
                'expected_yield_kg' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
                'status' => ['nullable', 'in:planning,planted,growing,ready_for_harvest,harvested'],
                'notes' => ['nullable', 'string', 'max:1000'],
            ]);

            Log::info('Debug Create Crop - Validation passed', [
                'validated' => $validated,
            ]);

            // Check farm
            $farm = Farm::find($validated['farm_id']);
            Log::info('Debug Create Crop - Farm check', [
                'farm_id' => $validated['farm_id'],
                'farm_exists' => $farm ? true : false,
                'farm_farmer_id' => $farm ? $farm->farmer_id : null,
                'user_id' => $user->id,
                'owner_match' => $farm && $farm->farmer_id === $user->id ? true : false,
            ]);

            if (!$farm || $farm->farmer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farm not found or not owned by user',
                    'debug' => [
                        'farm_id' => $validated['farm_id'],
                        'farm_exists' => $farm ? true : false,
                        'farm_farmer_id' => $farm ? $farm->farmer_id : null,
                        'user_id' => $user->id,
                    ],
                ], 404);
            }

            // Remove null values
            $data = array_filter($validated, fn($v) => $v !== null);
            if (!isset($data['status'])) {
                $data['status'] = 'planning';
            }

            Log::info('Debug Create Crop - Data to create', ['data' => $data]);

            // Try to create
            $crop = Crop::create($data);

            Log::info('Debug Create Crop - Successfully created', [
                'crop_id' => $crop->id,
            ]);

            $crop->load('farm');

            return response()->json([
                'success' => true,
                'message' => 'Crop created successfully',
                'data' => $crop,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Debug Create Crop - Validation error', [
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'debug' => ['type' => 'ValidationException'],
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Debug Create Crop - Exception', [
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating crop',
                'error' => $e->getMessage(),
                'debug' => [
                    'exception_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }

    /**
     * List farms for debugging
     */
    public function debugFarms(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $farms = Farm::where('farmer_id', $user->id)->get();
        
        return response()->json([
            'user_id' => $user->id,
            'farms_count' => $farms->count(),
            'farms' => $farms,
        ]);
    }
}
