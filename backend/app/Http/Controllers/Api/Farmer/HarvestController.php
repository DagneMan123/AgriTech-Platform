<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HarvestController extends Controller
{
    /**
     * Get all harvests for farmer
     */
    public function index(Request $request)
    {
        try {
            $farmerId = Auth::id();

            $harvests = DB::table('harvests as h')
                ->join('crops as c', 'h.crop_id', '=', 'c.id')
                ->join('farms as f', 'c.farm_id', '=', 'f.id')
                ->where('f.farmer_id', $farmerId)
                ->select(
                    'h.id',
                    'h.crop_id',
                    'h.harvest_date',
                    'h.quantity',
                    'h.quantity_harvested',
                    'h.unit',
                    'h.quality_grade',
                    'h.notes',
                    'h.harvest_notes',
                    'h.created_at',
                    'h.updated_at',
                    'c.id as crop_id_full',
                    'c.crop_type',
                    'c.variety',
                    'f.id as farm_id',
                    'f.name as farm_name'
                )
                ->orderBy('h.harvest_date', 'desc')
                ->limit(20)
                ->get();

            $data = $harvests->map(function ($h) {
                return [
                    'id' => $h->id,
                    'crop_id' => $h->crop_id,
                    'harvest_date' => $h->harvest_date,
                    'quantity' => (float) ($h->quantity ?? $h->quantity_harvested ?? 0),
                    'unit' => $h->unit,
                    'quality_grade' => $h->quality_grade,
                    'notes' => $h->notes ?? $h->harvest_notes,
                    'crop' => [
                        'id' => $h->crop_id_full,
                        'crop_type' => $h->crop_type,
                        'variety' => $h->variety,
                        'farm' => [
                            'id' => $h->farm_id,
                            'name' => $h->farm_name,
                        ]
                    ],
                    'created_at' => $h->created_at,
                    'updated_at' => $h->updated_at,
                ];
            });

            return response()->json(['success' => true, 'data' => $data->toArray()]);

        } catch (\Exception $e) {
            \Log::error('Harvest index: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Create harvest record
     */
    public function store(Request $request)
    {
        try {
            $farmerId = Auth::id();

            // Validate that crop belongs to farmer
            $crop = DB::table('crops as c')
                ->join('farms as f', 'c.farm_id', '=', 'f.id')
                ->where('c.id', $request->input('crop_id'))
                ->where('f.farmer_id', $farmerId)
                ->first();

            if (!$crop) {
                return response()->json(['success' => false, 'message' => 'Invalid crop'], 400);
            }

            // Insert harvest record directly
            $harvestId = DB::table('harvests')->insertGetId([
                'crop_id' => $request->input('crop_id'),
                'harvest_date' => $request->input('harvest_date'),
                'quantity_harvested' => $request->input('quantity', 0),
                'unit' => $request->input('unit', 'kg'),
                'quality_grade' => $request->input('quality_grade'),
                'harvest_notes' => $request->input('notes'),
                'number_of_workers' => 1, // Default value
                'storage_method' => 'fresh',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $harvest = DB::table('harvests')->where('id', $harvestId)->first();

            return response()->json([
                'success' => true,
                'message' => 'Harvest created successfully',
                'data' => $harvest
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Harvest store: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get harvest details
     */
    public function show($id)
    {
        try {
            $farmerId = Auth::id();

            $harvest = DB::table('harvests as h')
                ->join('crops as c', 'h.crop_id', '=', 'c.id')
                ->join('farms as f', 'c.farm_id', '=', 'f.id')
                ->where('h.id', $id)
                ->where('f.farmer_id', $farmerId)
                ->first();

            if (!$harvest) {
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }

            return response()->json(['success' => true, 'data' => $harvest]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update harvest
     */
    public function update(Request $request, $id)
    {
        try {
            $farmerId = Auth::id();

            $harvest = DB::table('harvests as h')
                ->join('crops as c', 'h.crop_id', '=', 'c.id')
                ->join('farms as f', 'c.farm_id', '=', 'f.id')
                ->where('h.id', $id)
                ->where('f.farmer_id', $farmerId)
                ->first();

            if (!$harvest) {
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }

            $updates = [];
            if ($request->has('harvest_date')) $updates['harvest_date'] = $request->input('harvest_date');
            if ($request->has('quantity')) {
                $updates['quantity'] = $request->input('quantity');
                $updates['quantity_harvested'] = $request->input('quantity');
            }
            if ($request->has('unit')) $updates['unit'] = $request->input('unit');
            if ($request->has('quality_grade')) $updates['quality_grade'] = $request->input('quality_grade');
            if ($request->has('notes')) {
                $updates['notes'] = $request->input('notes');
                $updates['harvest_notes'] = $request->input('notes');
            }
            $updates['updated_at'] = now();

            DB::table('harvests')->where('id', $id)->update($updates);

            $updated = DB::table('harvests')->where('id', $id)->first();

            return response()->json(['success' => true, 'message' => 'Updated', 'data' => $updated]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete harvest
     */
    public function destroy($id)
    {
        try {
            $farmerId = Auth::id();

            $harvest = DB::table('harvests as h')
                ->join('crops as c', 'h.crop_id', '=', 'c.id')
                ->join('farms as f', 'c.farm_id', '=', 'f.id')
                ->where('h.id', $id)
                ->where('f.farmer_id', $farmerId)
                ->first();

            if (!$harvest) {
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }

            DB::table('harvests')->where('id', $id)->delete();

            return response()->json(['success' => true, 'message' => 'Deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get harvest statistics
     */
    public function statistics(Request $request)
    {
        try {
            $farmerId = Auth::id();
            $year = $request->get('year', now()->year);

            $stats = DB::table('harvests as h')
                ->join('crops as c', 'h.crop_id', '=', 'c.id')
                ->join('farms as f', 'c.farm_id', '=', 'f.id')
                ->where('f.farmer_id', $farmerId)
                ->whereYear('h.harvest_date', $year)
                ->selectRaw('COUNT(*) as total_harvests')
                ->selectRaw('SUM(h.quantity) as total_quantity')
                ->selectRaw('AVG(h.quantity) as average_quantity')
                ->first();

            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
