<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\TransportRequest;
use App\Models\Transporter;
use App\Models\Vehicle;
use App\Http\Requests\Farmer\TransportationRequest;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    /**
     * Get all transport requests for the authenticated farmer
     */
    public function index(Request $request)
    {
        try {
            $farmer = $this->getOrCreateFarmer();

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $query = TransportRequest::where('farmer_id', $farmer->id)
                ->with(['product', 'transporter', 'vehicle'])
                ->latest();

            if ($request->filled('status')) {
                $status = $request->status;
                if ($status === 'confirmed') {
                    $query->whereIn('status', ['confirmed', 'assigned']);
                } elseif ($status === 'shipped') {
                    $query->whereIn('status', ['shipped', 'in_transit']);
                } elseif ($status === 'delivered') {
                    $query->whereIn('status', ['delivered', 'completed']);
                } else {
                    $query->where('status', $status);
                }
            }

            $transportRequests = $query->paginate(20);

            return response()->json([
                'data' => $transportRequests->items(),
                'pagination' => [
                    'total' => $transportRequests->total(),
                    'per_page' => $transportRequests->perPage(),
                    'current_page' => $transportRequests->currentPage(),
                    'last_page' => $transportRequests->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching transport requests', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a new transport request (Stage 1: Pending)
     */
    public function store(Request $request)
    {
        try {
            $farmer = $this->getOrCreateFarmer();

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            // Validate the request
            $validated = $request->validate([
                'product_name' => 'required|string|max:255',
                'quantity' => 'required|numeric|min:0.01',
                'unit' => 'required|in:kg,tonnes,bags,bundles,pieces',
                'pickup_location' => 'required|string|max:500',
                'delivery_location' => 'required|string|max:500',
                'preferred_date' => 'required|date|after_or_equal:today',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Stage 1: Initiated automatically as Pending, awaiting dispatch assignment
            $transportRequest = TransportRequest::create([
                'farmer_id' => $farmer->id,
                'product_id' => null,
                'product_name' => $validated['product_name'],
                'quantity' => $validated['quantity'],
                'unit' => $validated['unit'],
                'pickup_location' => $validated['pickup_location'],
                'delivery_location' => $validated['delivery_location'],
                'pickup_date' => $validated['preferred_date'],
                'delivery_date' => $validated['preferred_date'],
                'handling_instructions' => $validated['notes'] ?? null,
                'status' => 'pending',
                'vehicle_type' => 'other',
            ]);

            return response()->json([
                'message' => 'Transport request logged successfully in Stage 1: Pending',
                'data' => $transportRequest->load(['product', 'transporter', 'vehicle']),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating transport request', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a specific transport request
     */
    public function show(Request $request, $id)
    {
        try {
            $farmer = $this->getOrCreateFarmer();

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $transportRequest = TransportRequest::where('farmer_id', $farmer->id)
                ->with(['product', 'transporter', 'vehicle'])
                ->findOrFail($id);

            return response()->json(['data' => $transportRequest]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Transport request not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching transport request', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update transport request lifecycle status:
     * Stage 1: Pending -> Stage 2: Confirmed -> Stage 3: Shipped -> Stage 4: Delivered & Paid
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $farmer = $this->getOrCreateFarmer();

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $transportRequest = TransportRequest::where('farmer_id', $farmer->id)->findOrFail($id);

            $status = $request->input('status');
            $allowed = ['pending', 'confirmed', 'assigned', 'shipped', 'in_transit', 'delivered', 'completed', 'cancelled'];
            if (!in_array($status, $allowed)) {
                return response()->json(['message' => 'Invalid status provided'], 422);
            }

            // Normalize to the 4 canonical stages
            $canonicalStatus = match ($status) {
                'assigned' => 'confirmed',
                'in_transit' => 'shipped',
                'completed' => 'delivered',
                default => $status,
            };

            $updateData = ['status' => $canonicalStatus];

            // Stage 2: Confirmed - logistics manager reviews and approves request by assigning vehicle/transporter
            if ($canonicalStatus === 'confirmed') {
                $transporterId = $request->input('assigned_transporter_id') ?? $transportRequest->assigned_transporter_id;
                $vehicleId = $request->input('assigned_vehicle_id') ?? $transportRequest->assigned_vehicle_id;

                if (!$transporterId) {
                    $activeTransporter = Transporter::where('is_active', true)->first();
                    $transporterId = $activeTransporter?->id;
                    $vehicleId = $transporterId ? Vehicle::where('transporter_id', $transporterId)->value('id') : null;
                }

                $updateData['assigned_transporter_id'] = $transporterId;
                $updateData['assigned_vehicle_id'] = $vehicleId;
            }

            // Stage 3: Shipped - vehicle picks up produce from farm/warehouse and begins transit (Dispatch)
            if ($canonicalStatus === 'shipped') {
                $updateData['pickup_date'] = now()->toDateString();
            }

            // Stage 4: Delivered & Paid - receipt of goods confirmed and funds released
            if ($canonicalStatus === 'delivered') {
                $updateData['delivery_date'] = now()->toDateString();
            }

            $transportRequest->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Transport request successfully transitioned to ' . ucfirst($canonicalStatus),
                'data' => $transportRequest->fresh(['product', 'transporter', 'vehicle']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Transport request not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating transport status', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a transport request
     */
    public function update(Request $request, $id)
    {
        try {
            $farmer = $this->getOrCreateFarmer();

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $transportRequest = TransportRequest::where('farmer_id', $farmer->id)->findOrFail($id);

            // Validate the request
            $validated = $request->validate([
                'product_name' => 'required|string|max:255',
                'quantity' => 'required|numeric|min:0.01',
                'unit' => 'required|in:kg,tonnes,bags,bundles,pieces',
                'pickup_location' => 'required|string|max:500',
                'delivery_location' => 'required|string|max:500',
                'preferred_date' => 'required|date|after_or_equal:today',
                'notes' => 'nullable|string|max:1000',
            ]);

            $transportRequest->update([
                'product_name' => $validated['product_name'],
                'quantity' => $validated['quantity'],
                'unit' => $validated['unit'],
                'pickup_location' => $validated['pickup_location'],
                'delivery_location' => $validated['delivery_location'],
                'pickup_date' => $validated['preferred_date'],
                'handling_instructions' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'message' => 'Transport request updated successfully',
                'data' => $transportRequest->load(['product', 'transporter', 'vehicle']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Transport request not found'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating transport request', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a transport request
     */
    public function destroy(Request $request, $id)
    {
        try {
            $farmer = $this->getOrCreateFarmer();

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $transportRequest = TransportRequest::where('farmer_id', $farmer->id)->findOrFail($id);

            $transportRequest->delete();

            return response()->json(['message' => 'Transport request deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Transport request not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting transport request', 'error' => $e->getMessage()], 500);
        }
    }
}
