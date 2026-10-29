<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractMilestone;
use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    /**
     * Get all direct contracts for authenticated buyer
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = Contract::where('buyer_id', $userId)
            ->with(['farmer.user', 'milestones']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'ILIKE', $search)
                  ->orWhere('title', 'ILIKE', $search)
                  ->orWhere('crop_type', 'ILIKE', $search)
                  ->orWhereHas('farmer', function ($fq) use ($search) {
                      $fq->where('farm_name', 'ILIKE', $search)
                         ->orWhereHas('user', function ($uq) use ($search) {
                             $uq->where('name', 'ILIKE', $search);
                         });
                  });
            });
        }

        $sortBy = $request->get('sort', 'newest');
        switch ($sortBy) {
            case 'highest_value':
                $query->orderBy('total_amount', 'desc');
                break;
            case 'lowest_value':
                $query->orderBy('total_amount', 'asc');
                break;
            case 'delivery_date':
                $query->orderBy('delivery_date', 'asc');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $contracts = $query->paginate($request->get('limit', 20));

        // Aggregate statistics for stats cards
        $allContracts = Contract::where('buyer_id', $userId)->get();
        $stats = [
            'total_contracts' => $allContracts->count(),
            'active_contracts' => $allContracts->whereIn('status', ['active', 'in_progress'])->count(),
            'pending_contracts' => $allContracts->whereIn('status', ['proposed', 'draft'])->count(),
            'completed_contracts' => $allContracts->where('status', 'completed')->count(),
            'total_contracted_value' => (float) $allContracts->whereNotIn('status', ['cancelled'])->sum('total_amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $contracts,
            'stats' => $stats,
        ]);
    }

    /**
     * Get verified farmers eligible for direct contracting
     */
    public function farmers()
    {
        $farmers = Farmer::with(['user:id,name,phone,email'])
            ->select('id', 'user_id', 'farm_name', 'region', 'zone', 'woreda', 'farm_type', 'average_rating', 'verification_status')
            ->orderBy('farm_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $farmers,
        ]);
    }

    /**
     * Create a new direct farming contract
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'title' => 'required|string|max:255',
            'crop_type' => 'required|string|max:100',
            'variety' => 'nullable|string|max:100',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0.01',
            'advance_payment' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'delivery_date' => 'required|date|after_or_equal:start_date',
            'delivery_location' => 'nullable|string|max:255',
            'quality_specifications' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
        ]);

        $userId = Auth::id();
        $totalAmount = round($validated['quantity'] * $validated['unit_price'], 2);
        $advancePayment = $validated['advance_payment'] ?? 0;

        DB::beginTransaction();
        try {
            $contract = Contract::create([
                'buyer_id' => $userId,
                'farmer_id' => $validated['farmer_id'],
                'title' => $validated['title'],
                'crop_type' => $validated['crop_type'],
                'variety' => $validated['variety'] ?? null,
                'quantity' => $validated['quantity'],
                'unit' => $validated['unit'] ?? 'Quintal',
                'unit_price' => $validated['unit_price'],
                'total_amount' => $totalAmount,
                'advance_payment' => $advancePayment,
                'currency' => 'ETB',
                'start_date' => $validated['start_date'],
                'delivery_date' => $validated['delivery_date'],
                'delivery_location' => $validated['delivery_location'] ?? null,
                'quality_specifications' => $validated['quality_specifications'] ?? null,
                'status' => 'proposed',
                'payment_terms' => $validated['payment_terms'] ?? '30% Advance deposit, 70% upon successful delivery & quality verification',
                'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
                'signed_by_buyer_at' => now(),
            ]);

            // Automatically generate production milestones
            $milestones = [
                [
                    'title' => 'Land Preparation & Sowing',
                    'description' => 'Soil fertilization, certified seed sowing, and field preparation verification',
                    'due_date' => $contract->start_date,
                    'status' => 'pending',
                    'payout_percentage' => 30.00,
                    'payout_amount' => round($totalAmount * 0.30, 2),
                    'payout_status' => $advancePayment > 0 ? 'in_escrow' : 'unpaid',
                ],
                [
                    'title' => 'Mid-Season Crop Health & Growth Inspection',
                    'description' => 'Field monitoring for pest control, growth staging, and yield forecast check',
                    'due_date' => date('Y-m-d', strtotime($contract->start_date . ' + 45 days')),
                    'status' => 'pending',
                    'payout_percentage' => 0.00,
                    'payout_amount' => 0.00,
                    'payout_status' => 'unpaid',
                ],
                [
                    'title' => 'Harvesting, Threshing & Grading',
                    'description' => 'Produce harvest, sorting into specified grade quality, and standardized bagging',
                    'due_date' => date('Y-m-d', strtotime($contract->delivery_date . ' - 7 days')),
                    'status' => 'pending',
                    'payout_percentage' => 40.00,
                    'payout_amount' => round($totalAmount * 0.40, 2),
                    'payout_status' => 'unpaid',
                ],
                [
                    'title' => 'Final Warehouse Delivery & Escrow Settlement',
                    'description' => 'Delivery to buyer premises, final laboratory moisture/purity test, and final payment release',
                    'due_date' => $contract->delivery_date,
                    'status' => 'pending',
                    'payout_percentage' => 30.00,
                    'payout_amount' => round($totalAmount * 0.30, 2),
                    'payout_status' => 'unpaid',
                ],
            ];

            foreach ($milestones as $m) {
                $m['contract_id'] = $contract->id;
                ContractMilestone::create($m);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Direct contract proposal initiated and sent to farmer successfully',
                'data' => $contract->load(['farmer.user', 'milestones']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate contract: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific contract's details
     */
    public function show($id)
    {
        $userId = Auth::id();
        $contract = Contract::where('id', $id)
            ->where('buyer_id', $userId)
            ->with(['farmer.user', 'milestones'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $contract,
        ]);
    }

    /**
     * Cancel / terminate a contract
     */
    public function cancel(Request $request, $id)
    {
        $userId = Auth::id();
        $contract = Contract::where('id', $id)
            ->where('buyer_id', $userId)
            ->firstOrFail();

        if (in_array($contract->status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'This contract cannot be cancelled in its current state',
            ], 422);
        }

        $contract->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->get('reason', 'Cancelled by buyer'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contract cancelled successfully',
            'data' => $contract,
        ]);
    }
}
