<?php

namespace App\Http\Controllers\Api\Transport;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    public function dashboard(Request $request)
    {
        $transporter = $request->user();

        return response()->json([
            'active_deliveries' => \App\Models\Delivery::where('transport_provider_id', $transporter->id)
                ->where('status', 'in_transit')
                ->count(),
            'completed_deliveries' => \App\Models\Delivery::where('transport_provider_id', $transporter->id)
                ->where('status', 'delivered')
                ->count(),
        ]);
    }

    public function vehicles(Request $request)
    {
        $transporter = $request->user();
        $vehicles = Vehicle::where('owner_id', $transporter->id)->get();

        return response()->json($vehicles);
    }

    public function addVehicle(Request $request)
    {
        $validated = $request->validate([
            'registration_number' => 'required|unique:vehicles',
            'vehicle_type' => 'required|in:motorcycle,car,truck,van',
            'capacity_kg' => 'required|numeric',
        ]);

        $vehicle = Vehicle::create([
            'owner_id' => $request->user()->id,
            ...$validated,
        ]);

        return response()->json($vehicle, 201);
    }
}
