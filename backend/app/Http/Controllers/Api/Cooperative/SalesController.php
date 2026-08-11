<?php

namespace App\Http\Controllers\Api\Cooperative;

use App\Http\Controllers\Controller;
use App\Models\Cooperative;
use App\Models\CooperativeSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    /**
     * Get cooperative sales
     */
    public function index(Request $request)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative) {
            return response()->json([
                'success' => false,
                'message' => 'Cooperative not found',
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,completed,cancelled',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
            'sort' => 'sometimes|in:newest,oldest,amount_high,amount_low',
        ]);

        $query = $cooperative->sales()->with('buyer', 'items');

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (isset($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        if (isset($validated['sort'])) {
            match ($validated['sort']) {
                'newest' => $query->orderBy('created_at', 'desc'),
                'oldest' => $query->orderBy('created_at', 'asc'),
                'amount_high' => $query->orderBy('total_amount', 'desc'),
                'amount_low' => $query->orderBy('total_amount', 'asc'),
            };
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $sales = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $sales,
        ]);
    }

    /**
     * Create new sale
     */
    public function store(Request $request)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative) {
            return response()->json([
                'success' => false,
                'message' => 'Cooperative not found',
            ], 404);
        }

        $validated = $request->validate([
            'buyer_name' => 'required|string',
            'buyer_contact' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:1',
            'items.*.member_id' => 'sometimes|exists:cooperative_members,id',
            'delivery_date' => 'sometimes|date|after:today',
            'payment_terms' => 'sometimes|string',
            'notes' => 'sometimes|string',
        ]);

        $sale = CooperativeSale::create([
            'cooperative_id' => $cooperative->id,
            'buyer_name' => $validated['buyer_name'],
            'buyer_contact' => $validated['buyer_contact'],
            'total_amount' => 0,
            'status' => 'pending',
            'sale_date' => now(),
            'delivery_date' => $validated['delivery_date'] ?? now(),
            'payment_terms' => $validated['payment_terms'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            $subtotal = $item['quantity'] * $item['unit_price'];
            $totalAmount += $subtotal;

            $sale->items()->create([
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $subtotal,
                'member_id' => $item['member_id'] ?? null,
            ]);
        }

        $sale->update(['total_amount' => $totalAmount]);

        return response()->json([
            'success' => true,
            'message' => 'Sale created successfully',
            'data' => $sale->load('items'),
        ], 201);
    }

    /**
     * Get sale details
     */
    public function show(CooperativeSale $sale)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative || $sale->cooperative_id !== $cooperative->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sale not found',
            ], 404);
        }

        $sale->load('buyer', 'items', 'member');

        return response()->json([
            'success' => true,
            'data' => $sale,
        ]);
    }

    /**
     * Update sale
     */
    public function update(Request $request, CooperativeSale $sale)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative || $sale->cooperative_id !== $cooperative->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sale not found',
            ], 404);
        }

        if ($sale->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Can only update pending sales',
            ], 422);
        }

        $validated = $request->validate([
            'buyer_name' => 'sometimes|string',
            'buyer_contact' => 'sometimes|string',
            'delivery_date' => 'sometimes|date',
            'payment_terms' => 'sometimes|string',
            'notes' => 'sometimes|string',
        ]);

        $sale->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Sale updated successfully',
            'data' => $sale,
        ]);
    }

    /**
     * Confirm sale
     */
    public function confirm(Request $request, CooperativeSale $sale)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative || $sale->cooperative_id !== $cooperative->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sale not found',
            ], 404);
        }

        if ($sale->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Sale is not pending',
            ], 422);
        }

        $validated = $request->validate([
            'payment_received' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,check',
            'confirmation_notes' => 'sometimes|string',
        ]);

        $sale->update([
            'status' => 'completed',
            'payment_received' => $validated['payment_received'],
            'payment_method' => $validated['payment_method'],
            'completion_date' => now(),
            'confirmation_notes' => $validated['confirmation_notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sale confirmed',
            'data' => $sale,
        ]);
    }

    /**
     * Cancel sale
     */
    public function cancel(Request $request, CooperativeSale $sale)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative || $sale->cooperative_id !== $cooperative->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sale not found',
            ], 404);
        }

        if ($sale->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Sale is already cancelled',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $sale->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['reason'],
            'cancelled_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sale cancelled',
            'data' => $sale,
        ]);
    }

    /**
     * Get sales statistics
     */
    public function statistics(Request $request)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative) {
            return response()->json([
                'success' => false,
                'message' => 'Cooperative not found',
            ], 404);
        }

        $validated = $request->validate([
            'period' => 'sometimes|in:week,month,quarter,year',
        ]);

        $period = $validated['period'] ?? 'month';
        $startDate = match ($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
        };

        $sales = $cooperative->sales()
            ->where('created_at', '>=', $startDate)
            ->get();

        $stats = [
            'total_sales' => $sales->sum('total_amount'),
            'sales_count' => $sales->count(),
            'average_sale' => $sales->count() > 0 ? $sales->avg('total_amount') : 0,
            'highest_sale' => $sales->max('total_amount'),
            'lowest_sale' => $sales->min('total_amount'),
            'completed_sales' => $sales->where('status', 'completed')->count(),
            'pending_sales' => $sales->where('status', 'pending')->count(),
            'cancelled_sales' => $sales->where('status', 'cancelled')->count(),
            'total_payment_received' => $sales->where('status', 'completed')->sum('payment_received'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get sales by product
     */
    public function byProduct()
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative) {
            return response()->json([
                'success' => false,
                'message' => 'Cooperative not found',
            ], 404);
        }

        $sales = $cooperative->sales()
            ->with('items')
            ->get();

        $byProduct = $sales->flatMap(function ($sale) {
            return $sale->items;
        })->groupBy('product_name')
            ->map(fn ($items) => [
                'product_name' => $items->first()->product_name,
                'total_quantity' => $items->sum('quantity'),
                'total_sales' => $items->sum('subtotal'),
                'avg_unit_price' => $items->avg('unit_price'),
                'sales_count' => $items->count(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $byProduct,
        ]);
    }

    /**
     * Export sales report
     */
    public function export(Request $request)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative) {
            return response()->json([
                'success' => false,
                'message' => 'Cooperative not found',
            ], 404);
        }

        $validated = $request->validate([
            'format' => 'required|in:csv,xlsx,pdf',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
        ]);

        $sales = $cooperative->sales()
            ->whereBetween('created_at', [
                $validated['date_from'],
                $validated['date_to'],
            ])
            ->with('items')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Export prepared',
            'data' => [
                'total_records' => $sales->count(),
                'format' => $validated['format'],
                'total_sales' => $sales->sum('total_amount'),
            ],
        ]);
    }
}
