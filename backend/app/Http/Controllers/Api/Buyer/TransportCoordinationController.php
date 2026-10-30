<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\TransportRequest;
use App\Models\Delivery;
use App\Models\Transporter;
use App\Models\Vehicle;
use App\Models\Order;
use App\Models\DeliveryAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransportCoordinationController extends Controller
{
    /**
     * Get all transport coordination requests and deliveries for the authenticated buyer from the database.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Fetch direct transport requests created by this buyer
        $transportRequests = TransportRequest::where('buyer_id', $user->id)
            ->with(['product', 'transporter', 'vehicle'])
            ->latest()
            ->get();

        // 2. Also fetch deliveries linked to the buyer's marketplace orders
        $orderDeliveries = Delivery::whereHas('order', fn($q) => $q->where('buyer_id', $user->id))
            ->with(['order.items.product', 'order.farm', 'vehicle', 'tracking'])
            ->latest()
            ->get();

        // Transform into a unified coordination format
        $unifiedList = collect();

        foreach ($transportRequests as $req) {
            $unifiedList->push([
                'id' => $req->id,
                'type' => 'freight_request',
                'tracking_number' => 'TRQ-' . str_pad($req->id, 5, '0', STR_PAD_LEFT),
                'product_name' => $req->product_name ?? ($req->product->name ?? 'Agricultural Cargo'),
                'quantity' => $req->quantity,
                'unit' => $req->unit,
                'pickup_location' => $req->pickup_location,
                'delivery_location' => $req->delivery_location,
                'pickup_date' => $req->pickup_date ? $req->pickup_date->format('Y-m-d') : null,
                'delivery_date' => $req->delivery_date ? $req->delivery_date->format('Y-m-d') : null,
                'vehicle_type' => $req->vehicle_type,
                'special_handling' => (bool)$req->special_handling,
                'handling_instructions' => $req->handling_instructions,
                'estimated_cost' => $req->estimated_cost ? (float)$req->estimated_cost : 0,
                'contact_person' => $req->contact_person,
                'contact_phone' => $req->contact_phone,
                'status' => $req->status, // pending, assigned, in_transit, delivered, cancelled
                'transporter_name' => $req->transporter->company_name ?? ($req->transporter->contact_person ?? 'Unassigned'),
                'transporter_phone' => $req->transporter->emergency_contact ?? ($req->transporter->user->phone ?? null),
                'vehicle_plate' => $req->vehicle->plate_number ?? ($req->transporter->license_plate ?? 'Pending Allocation'),
                'created_at' => $req->created_at->toISOString(),
            ]);
        }

        // Add order deliveries that aren't already represented
        foreach ($orderDeliveries as $del) {
            $productNames = $del->order && $del->order->items
                ? $del->order->items->map(fn($i) => $i->product->name ?? 'Produce')->join(', ')
                : 'Order #' . $del->order_id . ' Produce';

            $unifiedList->push([
                'id' => 'del-' . $del->id,
                'delivery_id' => $del->id,
                'order_id' => $del->order_id,
                'type' => 'order_delivery',
                'tracking_number' => $del->tracking_info['tracking_number'] ?? ('TRK-' . str_pad($del->id, 5, '0', STR_PAD_LEFT)),
                'product_name' => $productNames,
                'quantity' => $del->order ? $del->order->items->sum('quantity') : 1,
                'unit' => 'Quintal',
                'pickup_location' => $del->pickup_location ?? ($del->order->farm->name ?? 'Farm Origin Hub'),
                'delivery_location' => $del->delivery_address ?? ($del->delivery_location ?? 'Buyer Commercial Destination'),
                'pickup_date' => $del->pickup_time ? $del->pickup_time->format('Y-m-d') : null,
                'delivery_date' => $del->estimated_delivery_time ? $del->estimated_delivery_time->format('Y-m-d') : null,
                'vehicle_type' => $del->vehicle->type ?? 'Freight Truck',
                'special_handling' => false,
                'handling_instructions' => $del->notes,
                'estimated_cost' => (float)($del->delivery_fee ?? 0),
                'contact_person' => $del->driver_name ?? ($del->transporter->name ?? 'Assigned Transporter'),
                'contact_phone' => $del->driver_phone ?? ($del->transporter->phone ?? '+251 91 000 0000'),
                'status' => $del->status === 'delivered' ? 'completed' : ($del->status === 'in_transit' ? 'in_transit' : 'assigned'),
                'transporter_name' => $del->driver_name ?? ($del->transporter->name ?? 'National Fleet Carrier'),
                'transporter_phone' => $del->driver_phone ?? ($del->transporter->phone ?? null),
                'vehicle_plate' => $del->vehicle->plate_number ?? 'Fleet Truck #4',
                'created_at' => $del->created_at->toISOString(),
            ]);
        }

        // Calculate statistics from database records
        $stats = [
            'total' => $unifiedList->count(),
            'pending' => $unifiedList->where('status', 'pending')->count(),
            'confirmed' => $unifiedList->whereIn('status', ['confirmed', 'assigned'])->count(),
            'in_transit' => $unifiedList->where('status', 'in_transit')->count(),
            'completed' => $unifiedList->whereIn('status', ['completed', 'delivered'])->count(),
            'total_freight_cost' => $unifiedList->sum('estimated_cost'),
            'total_quantity' => $unifiedList->sum('quantity')
        ];

        return response()->json([
            'success' => true,
            'data' => $unifiedList->values(),
            'stats' => $stats
        ]);
    }

    /**
     * Store a new transport coordination booking in the database.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.1',
            'unit' => 'required|string|max:50',
            'pickup_location' => 'required|string',
            'delivery_location' => 'required|string',
            'pickup_date' => 'required|date',
            'delivery_date' => 'required|date|after_or_equal:pickup_date',
            'vehicle_type' => 'required|string|max:100',
            'special_handling' => 'nullable|boolean',
            'handling_instructions' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'contact_person' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Try to auto-assign an active transporter in the area if available
        $transporter = Transporter::where('is_active', true)->inRandomOrder()->first();
        $vehicle = $transporter ? Vehicle::where('transporter_id', $transporter->id)->first() : null;

        $transportRequest = TransportRequest::create([
            'buyer_id' => $user->id,
            'product_name' => $request->input('product_name'),
            'quantity' => $request->input('quantity'),
            'unit' => $request->input('unit', 'Quintal'),
            'pickup_location' => $request->input('pickup_location'),
            'delivery_location' => $request->input('delivery_location'),
            'pickup_date' => $request->input('pickup_date'),
            'delivery_date' => $request->input('delivery_date'),
            'vehicle_type' => $request->input('vehicle_type'),
            'special_handling' => $request->boolean('special_handling'),
            'handling_instructions' => $request->input('handling_instructions'),
            'estimated_cost' => $request->input('estimated_cost', 0),
            'contact_person' => $request->input('contact_person'),
            'contact_phone' => $request->input('contact_phone'),
            'status' => $transporter ? 'assigned' : 'pending',
            'assigned_transporter_id' => $transporter?->id,
            'assigned_vehicle_id' => $vehicle?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transport coordination request registered in database successfully',
            'data' => $transportRequest
        ], 201);
    }

    /**
     * Cancel an active transport coordination request.
     */
    public function cancel(Request $request, $id)
    {
        $user = $request->user();

        $req = TransportRequest::where('buyer_id', $user->id)->findOrFail($id);

        if (in_array($req->status, ['in_transit', 'delivered', 'completed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel transport that is already in transit or completed.'
            ], 400);
        }

        $req->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Transport coordination booking cancelled successfully.'
        ]);
    }

    /**
     * Get verified transporters and available fleet options from the database.
     */
    public function transporters(Request $request)
    {
        $transporters = Transporter::with('user')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transporters
        ]);
    }
}
