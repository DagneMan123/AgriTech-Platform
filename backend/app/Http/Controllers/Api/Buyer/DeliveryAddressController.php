<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAddress;
use App\Models\Buyer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeliveryAddressController extends Controller
{
    /**
     * Get all delivery addresses for the authenticated buyer from the database.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $addresses = DeliveryAddress::where('user_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // If the buyer has no addresses registered in the database yet,
        // seed with their registered user/buyer address or latest order delivery address
        if ($addresses->isEmpty()) {
            $buyerProfile = Buyer::where('user_id', $user->id)->first();
            $latestOrder = Order::where('buyer_id', $user->id)
                ->whereNotNull('delivery_address')
                ->latest()
                ->first();

            $baseAddress = $latestOrder->delivery_address
                ?? $buyerProfile->business_address
                ?? $user->address
                ?? null;

            if ($baseAddress) {
                $defaultAddress = DeliveryAddress::create([
                    'user_id' => $user->id,
                    'title' => $buyerProfile->business_name ?? ($user->name . ' Primary Hub'),
                    'type' => 'warehouse',
                    'contact_name' => $user->name,
                    'contact_role' => 'Receiving Operations Manager',
                    'contact_phone' => $buyerProfile->business_phone ?? ($user->phone ?? '+251 91 100 0000'),
                    'region' => $user->region ?? 'Addis Ababa',
                    'city' => $user->location ?? 'Addis Ababa',
                    'sub_city' => $user->zone ?? 'Bole Sub-City',
                    'woreda_kebele' => $user->woreda ?? 'Woreda 03',
                    'street_address' => $baseAddress,
                    'landmark' => 'Registered Commercial Operating Facility',
                    'is_default' => true,
                    'instructions' => 'Gate receiving dock. Transporter consignment note check on entry.',
                    'receiving_hours' => 'Mon - Sat: 08:00 - 18:00',
                    'capabilities' => ['Forklift Available', 'Pallet Jack', 'Weighbridge on-site'],
                    'latitude' => 8.9806,
                    'longitude' => 38.7578,
                    'total_deliveries' => Order::where('buyer_id', $user->id)->count()
                ]);

                $addresses = collect([$defaultAddress]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }

    /**
     * Store a newly created delivery address in the database.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'contact_name' => 'required|string|max:255',
            'contact_role' => 'nullable|string|max:255',
            'contact_phone' => 'required|string|max:50',
            'secondary_phone' => 'nullable|string|max:50',
            'region' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'sub_city' => 'nullable|string|max:100',
            'woreda_kebele' => 'nullable|string|max:100',
            'street_address' => 'required|string',
            'landmark' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'is_default' => 'nullable|boolean',
            'instructions' => 'nullable|string',
            'receiving_hours' => 'nullable|string|max:100',
            'capabilities' => 'nullable|array',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $isDefault = $request->boolean('is_default');

        // If this is the first address, make it default automatically
        $existingCount = DeliveryAddress::where('user_id', $user->id)->count();
        if ($existingCount === 0) {
            $isDefault = true;
        }

        if ($isDefault) {
            DeliveryAddress::where('user_id', $user->id)->update(['is_default' => false]);
        }

        $address = DeliveryAddress::create([
            'user_id' => $user->id,
            'title' => $request->input('title'),
            'type' => $request->input('type', 'warehouse'),
            'contact_name' => $request->input('contact_name'),
            'contact_role' => $request->input('contact_role'),
            'contact_phone' => $request->input('contact_phone'),
            'secondary_phone' => $request->input('secondary_phone'),
            'region' => $request->input('region', 'Addis Ababa'),
            'city' => $request->input('city', 'Addis Ababa'),
            'sub_city' => $request->input('sub_city'),
            'woreda_kebele' => $request->input('woreda_kebele'),
            'street_address' => $request->input('street_address'),
            'landmark' => $request->input('landmark'),
            'postal_code' => $request->input('postal_code'),
            'is_default' => $isDefault,
            'instructions' => $request->input('instructions'),
            'receiving_hours' => $request->input('receiving_hours'),
            'capabilities' => $request->input('capabilities', []),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'total_deliveries' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery address created successfully in database',
            'data' => $address
        ], 201);
    }

    /**
     * Update an existing delivery address in the database.
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $address = DeliveryAddress::where('user_id', $user->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'type' => 'nullable|string|max:50',
            'contact_name' => 'sometimes|required|string|max:255',
            'contact_role' => 'nullable|string|max:255',
            'contact_phone' => 'sometimes|required|string|max:50',
            'secondary_phone' => 'nullable|string|max:50',
            'region' => 'sometimes|required|string|max:100',
            'city' => 'sometimes|required|string|max:100',
            'sub_city' => 'nullable|string|max:100',
            'woreda_kebele' => 'nullable|string|max:100',
            'street_address' => 'sometimes|required|string',
            'landmark' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'is_default' => 'nullable|boolean',
            'instructions' => 'nullable|string',
            'receiving_hours' => 'nullable|string|max:100',
            'capabilities' => 'nullable|array',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('is_default') && $request->boolean('is_default')) {
            DeliveryAddress::where('user_id', $user->id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->fill($request->only([
            'title', 'type', 'contact_name', 'contact_role', 'contact_phone',
            'secondary_phone', 'region', 'city', 'sub_city', 'woreda_kebele',
            'street_address', 'landmark', 'postal_code', 'is_default',
            'instructions', 'receiving_hours', 'capabilities', 'latitude', 'longitude'
        ]));

        $address->save();

        return response()->json([
            'success' => true,
            'message' => 'Delivery address updated successfully in database',
            'data' => $address
        ]);
    }

    /**
     * Remove the specified address from the database.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $address = DeliveryAddress::where('user_id', $user->id)->findOrFail($id);
        $wasDefault = $address->is_default;

        $address->delete();

        // If the deleted address was the default, make another one default
        if ($wasDefault) {
            $newDefault = DeliveryAddress::where('user_id', $user->id)->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Delivery address removed successfully from database'
        ]);
    }

    /**
     * Mark an address as the default delivery destination in the database.
     */
    public function setDefault(Request $request, $id)
    {
        $user = $request->user();

        $address = DeliveryAddress::where('user_id', $user->id)->findOrFail($id);

        DeliveryAddress::where('user_id', $user->id)->update(['is_default' => false]);

        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Address set as primary default destination in database',
            'data' => $address
        ]);
    }
}
