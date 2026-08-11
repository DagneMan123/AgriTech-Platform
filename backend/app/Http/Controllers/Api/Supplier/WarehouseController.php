<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    /**
     * Get supplier's warehouses
     */
    public function index(Request $request)
    {
        $warehouses = Warehouse::where('supplier_id', Auth::id())
            ->with('stocks')
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $warehouses,
        ]);
    }

    /**
     * Create warehouse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'capacity' => 'required|numeric|min:0',
            'manager_name' => 'required|string',
            'contact_phone' => 'required|string',
        ]);

        $warehouse = Warehouse::create([
            'supplier_id' => Auth::id(),
            ...$validated,
            'current_stock' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse created successfully',
            'data' => $warehouse,
        ], 201);
    }

    /**
     * Get warehouse details
     */
    public function show(Warehouse $warehouse)
    {
        $this->authorize('view', $warehouse);

        return response()->json([
            'success' => true,
            'data' => $warehouse->load('stocks.product'),
        ]);
    }

    /**
     * Update warehouse
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $this->authorize('update', $warehouse);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'location' => 'sometimes|string',
            'address' => 'sometimes|string',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'capacity' => 'sometimes|numeric|min:0',
            'manager_name' => 'sometimes|string',
            'contact_phone' => 'sometimes|string',
        ]);

        $warehouse->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse updated successfully',
            'data' => $warehouse,
        ]);
    }

    /**
     * Delete warehouse
     */
    public function destroy(Warehouse $warehouse)
    {
        $this->authorize('delete', $warehouse);

        if ($warehouse->stocks()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete warehouse with stock items',
            ], 422);
        }

        $warehouse->delete();

        return response()->json([
            'success' => true,
            'message' => 'Warehouse deleted successfully',
        ]);
    }

    /**
     * Get warehouse stock
     */
    public function stock(Warehouse $warehouse)
    {
        $this->authorize('view', $warehouse);

        $stocks = $warehouse->stocks()
            ->with('product')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $stocks,
        ]);
    }

    /**
     * Update warehouse stock
     */
    public function updateStock(Request $request, Warehouse $warehouse)
    {
        $this->authorize('update', $warehouse);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $stock = $warehouse->stocks()
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($stock) {
            $stock->update(['quantity' => $validated['quantity']]);
        } else {
            $warehouse->stocks()->create($validated);
        }

        // Update total warehouse stock
        $totalStock = $warehouse->stocks()->sum('quantity');
        $warehouse->update(['current_stock' => $totalStock]);

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully',
            'data' => $warehouse,
        ]);
    }

    /**
     * Get warehouse utilization
     */
    public function utilization(Warehouse $warehouse)
    {
        $this->authorize('view', $warehouse);

        $utilization = $warehouse->capacity > 0 
            ? ($warehouse->current_stock / $warehouse->capacity) * 100 
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'warehouse_id' => $warehouse->id,
                'name' => $warehouse->name,
                'capacity' => $warehouse->capacity,
                'current_stock' => $warehouse->current_stock,
                'available_space' => $warehouse->capacity - $warehouse->current_stock,
                'utilization_percent' => round($utilization, 2),
            ],
        ]);
    }
}
