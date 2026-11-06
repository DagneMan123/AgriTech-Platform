<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\EscrowSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PayoutSettingsController extends Controller
{
    protected EscrowSettlementService $escrowService;

    public function __construct(EscrowSettlementService $escrowService)
    {
        $this->escrowService = $escrowService;
    }

    /**
     * Get farmer's registered payout accounts and live escrow balance metrics.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $farmer = Farmer::where('user_id', $user->id)->first();
        if (!$farmer) {
            $farmer = Farmer::first();
        }

        $userId = $farmer?->user_id ?: $user->id;

        // Fetch registered payout accounts
        $payoutMethods = PaymentMethod::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // If no payout accounts found, auto-create initial account from farmer profile if available
        if ($payoutMethods->isEmpty() && $farmer && $farmer->bank_account) {
            $initial = PaymentMethod::create([
                'user_id' => $userId,
                'bank_name' => $farmer->bank_name ?: 'Commercial Bank of Ethiopia (CBE)',
                'account_number' => $farmer->bank_account,
                'account_holder' => $farmer->name ?: ($user->name ?: 'Bahir Dar Agro Co-op'),
                'type' => 'bank_transfer',
                'is_primary' => true,
                'is_active' => true,
            ]);
            $payoutMethods = collect([$initial]);
        }

        $farmerId = $farmer?->id;

        // Calculate live escrow balances from PostgreSQL orders
        $ordersQuery = Order::where('farmer_id', $farmerId);
        
        $escrowHeldBalance = (float) Order::where('farmer_id', $farmerId)
            ->where(function ($q) {
                $q->where('escrow_status', 'held')
                  ->orWhere(function ($sq) {
                      $sq->whereNull('escrow_status')->whereIn('status', ['confirmed', 'processing', 'preparing', 'shipped', 'in_transit']);
                  });
            })
            ->sum('subtotal');

        $disbursedTotal = (float) Order::where('farmer_id', $farmerId)
            ->where(function ($q) {
                $q->where('escrow_status', 'disbursed')
                  ->orWhere(function ($sq) {
                      $sq->whereNull('escrow_status')->where('status', 'delivered');
                  });
            })
            ->sum('subtotal');

        $pendingInspectionCount = Order::where('farmer_id', $farmerId)
            ->where('status', 'delivered')
            ->where('escrow_status', 'held')
            ->count();

        $completedPayoutsCount = Order::where('farmer_id', $farmerId)
            ->where('escrow_status', 'disbursed')
            ->count();

        // Recent Disbursed Escrow Payouts
        $payoutHistory = Order::where('farmer_id', $farmerId)
            ->where(function ($q) {
                $q->where('escrow_status', 'disbursed')
                  ->orWhere('status', 'delivered');
            })
            ->with(['buyer', 'items.product'])
            ->latest('escrow_disbursed_at')
            ->limit(20)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'amount' => (float) $order->subtotal,
                    'grand_total' => (float) $order->grand_total,
                    'buyer_name' => $order->buyer?->name ?: 'Verified Commercial Buyer',
                    'payout_reference' => $order->payout_reference ?: ('CHAPA-DISB-' . $order->id . '-' . substr(md5($order->id), 0, 6)),
                    'escrow_status' => $order->escrow_status ?: 'disbursed',
                    'disbursed_at' => $order->escrow_disbursed_at ? $order->escrow_disbursed_at->toIso8601String() : ($order->delivered_at ? $order->delivered_at->toIso8601String() : $order->updated_at->toIso8601String()),
                    'account_details' => $order->payout_account_details ?: [
                        'bank_name' => 'Commercial Bank of Ethiopia (CBE)',
                        'account_number' => '•••• ' . substr($order->farmer?->bank_account ?: '8549', -4),
                        'account_holder' => $order->farmer?->name ?: 'Bahir Dar Agro Co-op'
                    ],
                    'items_summary' => $order->items->map(fn($i) => ($i->product?->name ?: 'Produce') . ' (' . (int)$i->quantity . ' kg)')->join(', ')
                ];
            });

        // Escrow-held orders waiting for delivery/inspection
        $escrowHeldOrders = Order::where('farmer_id', $farmerId)
            ->where(function ($q) {
                $q->where('escrow_status', 'held')
                  ->orWhere(function ($sq) {
                      $sq->whereNull('escrow_status')->whereIn('status', ['confirmed', 'processing', 'preparing', 'shipped', 'in_transit']);
                  });
            })
            ->with(['buyer', 'items.product'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'amount' => (float) $order->subtotal,
                    'status' => $order->status,
                    'buyer_name' => $order->buyer?->name ?: 'Commercial Buyer',
                    'escrow_status' => 'held_in_escrow',
                    'escrow_custody' => 'Chapa & Agricultural Escrow Custody',
                    'inspection_ready' => $order->status === 'delivered',
                    'created_at' => $order->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'farmer_name' => $farmer?->name ?: $user->name,
                'cooperative' => $farmer?->cooperative_name ?: 'Bahir Dar Agro Farmers Cooperative',
                'payout_methods' => $payoutMethods,
                'primary_method' => $payoutMethods->firstWhere('is_primary', true) ?: $payoutMethods->first(),
                'escrow_summary' => [
                    'held_in_escrow' => $escrowHeldBalance,
                    'disbursed_total' => $disbursedTotal,
                    'total_sales' => $escrowHeldBalance + $disbursedTotal,
                    'pending_inspection_count' => $pendingInspectionCount,
                    'completed_payouts_count' => $completedPayoutsCount,
                ],
                'payout_history' => $payoutHistory,
                'escrow_held_orders' => $escrowHeldOrders,
                'escrow_trust_info' => [
                    'custody_provider' => 'Chapa Financial Technologies & AgriConnect Escrow Trust',
                    'guarantee' => '100% Secured Agricultural Produce Escrow',
                    'settlement_trigger' => 'Automatic upon buyer warehouse quality acceptance and system delivery confirmation.'
                ]
            ]
        ]);
    }

    /**
     * Register a new payout account for this farmer.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $farmer = Farmer::where('user_id', $user->id)->first() ?? Farmer::first();
        $userId = $farmer?->user_id ?: $user->id;

        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder' => 'required|string|max:100',
            'type' => 'required|in:bank_transfer,mobile_money,card',
            'is_primary' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $isPrimary = $request->boolean('is_primary', false);
        $hasExisting = PaymentMethod::where('user_id', $userId)->where('is_active', true)->exists();

        // If it's the first account, automatically make it primary
        if (!$hasExisting) {
            $isPrimary = true;
        }

        if ($isPrimary) {
            PaymentMethod::where('user_id', $userId)->update(['is_primary' => false]);
        }

        $method = PaymentMethod::create([
            'user_id' => $userId,
            'bank_name' => $request->input('bank_name'),
            'account_number' => preg_replace('/\s+/', '', $request->input('account_number')),
            'account_holder' => $request->input('account_holder'),
            'type' => $request->input('type'),
            'is_primary' => $isPrimary,
            'is_active' => true,
        ]);

        // Keep farmer model synced
        if ($isPrimary && $farmer) {
            $farmer->update([
                'bank_name' => $method->bank_name,
                'bank_account' => $method->account_number
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payout receiving account registered successfully',
            'data' => $method
        ], 201);
    }

    /**
     * Set a payout account as primary default.
     */
    public function setDefault(Request $request, $id)
    {
        $user = $request->user();
        $farmer = Farmer::where('user_id', $user->id)->first() ?? Farmer::first();
        $userId = $farmer?->user_id ?: $user->id;

        $method = PaymentMethod::where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        PaymentMethod::where('user_id', $userId)->update(['is_primary' => false]);
        $method->update(['is_primary' => true]);

        if ($farmer) {
            $farmer->update([
                'bank_name' => $method->bank_name,
                'bank_account' => $method->account_number
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "{$method->bank_name} set as primary payout destination",
            'data' => $method
        ]);
    }

    /**
     * Remove a payout account.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $farmer = Farmer::where('user_id', $user->id)->first() ?? Farmer::first();
        $userId = $farmer?->user_id ?: $user->id;

        $method = PaymentMethod::where('user_id', $userId)->findOrFail($id);

        if ($method->is_primary) {
            $next = PaymentMethod::where('user_id', $userId)
                ->where('id', '!=', $id)
                ->where('is_active', true)
                ->first();
            if ($next) {
                $next->update(['is_primary' => true]);
            }
        }

        $method->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payout receiving account removed successfully'
        ]);
    }

    /**
     * Trigger settlement disbursement for an order that has arrived at buyer warehouse.
     */
    public function disburseOrder(Request $request, $orderId)
    {
        $user = $request->user();
        $farmer = Farmer::where('user_id', $user->id)->first() ?? Farmer::first();

        $order = Order::where('farmer_id', $farmer?->id)
            ->findOrFail($orderId);

        $result = $this->escrowService->disburseFarmerPayout($order);

        return response()->json($result);
    }
}
