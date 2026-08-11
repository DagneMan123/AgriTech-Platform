<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Get supplier inventory
     */
    public function index(Request $request)
    {
        $inventory = Inventory::whereHas('product', function ($q) {
            $q->where('seller_id', Auth::id());
        })
        ->with('product', 'warehouse')
        ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $inventory,
        ]);
    }

    /**
     * Create inventory record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'sometimes|integer|min:0',
        ]);

        // Verify product ownership
        $product = Product::findOrFail($validated['product_id']);
        if ($product->seller_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $inventory = Inventory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Inventory created successfully',
            'data' => $inventory,
        ], 201);
    }

    /**
     * Update inventory
     */
    public function update(Request $request, Inventory $inventory)
    {
        $this->authorize('update', $inventory);

        $validated = $request->validate([
            'quantity' => 'sometimes|integer|min:0',
            'reorder_level' => 'sometimes|integer|min:0',
        ]);

        $inventory->update($validated);

        // Update product quantity
        if (isset($validated['quantity'])) {
            $product = $inventory->product;
            $product->update(['quantity_available' => $validated['quantity']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Inventory updated successfully',
            'data' => $inventory,
        ]);
    }

    /**
     * Get low stock items
     */
    public function lowStock()
    {
        $lowStock = Inventory::whereHas('product', function ($q) {
            $q->where('seller_id', Auth::id());
        })
        ->whereRaw('quantity < reorder_level')
        ->with('product', 'warehouse')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $lowStock,
        ]);
    }

    /**
     * Record stock adjustment
     */
    public function adjust(Request $request, Inventory $inventory)
    {
        $this->authorize('update', $inventory);

        $validated = $request->validate([
            'quantity_change' => 'required|integer',
            'reason' => 'required|string',
            'notes' => 'sometimes|string',
        ]);

        $oldQuantity = $inventory->quantity;
        $newQuantity = max(0, $oldQuantity + $validated['quantity_change']);

        $inventory->update(['quantity' => $newQuantity]);

        // Log the adjustment
        \App\Models\InventoryAdjustment::create([
            'inventory_id' => $inventory->id,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'quantity_change' => $validated['quantity_change'],
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? null,
            'adjusted_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock adjusted successfully',
            'data' => $inventory,
        ]);
    }

    /**
     * Get inventory statistics
     */
    public function statistics()
    {
        $supplierId = Auth::id();

        $stats = [
            'total_items' => Inventory::whereHas('product', function ($q) use ($supplierId) {
                $q->where('seller_id', $supplierId);
            })->count(),
            'total_quantity' => Inventory::whereHas('product', function ($q) use ($supplierId) {
                $q->where('seller_id', $supplierId);
            })->sum('quantity'),
            'low_stock_count' => Inventory::whereHas('product', function ($q) use ($supplierId) {
                $q->where('seller_id', $supplierId);
            })->whereRaw('quantity < reorder_level')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
