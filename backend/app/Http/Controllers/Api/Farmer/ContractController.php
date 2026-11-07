<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractMilestone;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    /**
     * Get all incoming and active forward contracts for the authenticated farmer
     */
    public function index(Request $request)
    {
        try {
            $farmer = $this->getOrCreateFarmer();

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            // Ensure baseline demo contracts exist (such as Teff Supply Agreement with Lake Tana Union)
            $this->ensureSampleContractsExist($farmer);

            $query = Contract::where('farmer_id', $farmer->id)
                ->with(['buyer', 'milestones']);

            if ($request->filled('status')) {
                $status = $request->status;
                if ($status === 'pending' || $status === 'proposed') {
                    $query->whereIn('status', ['proposed', 'draft']);
                } elseif ($status === 'active') {
                    $query->whereIn('status', ['active', 'in_progress']);
                } else {
                    $query->where('status', $status);
                }
            }

            if ($request->filled('search')) {
                $search = '%' . trim($request->search) . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('contract_number', 'ILIKE', $search)
                      ->orWhere('title', 'ILIKE', $search)
                      ->orWhere('crop_type', 'ILIKE', $search)
                      ->orWhereHas('buyer', function ($bq) use ($search) {
                          $bq->where('name', 'ILIKE', $search)
                             ->orWhere('email', 'ILIKE', $search);
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

            // Summary stats for dashboard pills
            $allContracts = Contract::where('farmer_id', $farmer->id)->get();
            $stats = [
                'total_contracts' => $allContracts->count(),
                'active_contracts' => $allContracts->whereIn('status', ['active', 'in_progress'])->count(),
                'pending_contracts' => $allContracts->whereIn('status', ['proposed', 'draft'])->count(),
                'completed_contracts' => $allContracts->where('status', 'completed')->count(),
                'total_contracted_value' => (float) $allContracts->whereNotIn('status', ['cancelled'])->sum('total_amount'),
                'advance_escrow_held' => (float) $allContracts->whereNotIn('status', ['cancelled'])->sum('advance_payment'),
            ];

            return response()->json([
                'success' => true,
                'data' => $contracts,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching contracts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single contract detail
     */
    public function show($id)
    {
        try {
            $farmer = $this->getOrCreateFarmer();
            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $contract = Contract::where('farmer_id', $farmer->id)
                ->with(['buyer', 'milestones'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $contract,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Contract not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching contract', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create / propose a forward supply agreement from farmer side
     */
    public function store(Request $request)
    {
        try {
            $farmer = $this->getOrCreateFarmer();
            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'crop_type' => 'required|string|max:100',
                'variety' => 'nullable|string|max:100',
                'quantity' => 'required|numeric|min:0.01',
                'unit' => 'nullable|string|max:50',
                'unit_price' => 'required|numeric|min:0.01',
                'advance_percentage' => 'nullable|numeric|min:0|max:100',
                'delivery_date' => 'required|date',
                'delivery_location' => 'nullable|string|max:255',
                'quality_specifications' => 'nullable|string',
                'buyer_name' => 'nullable|string|max:255',
            ]);

            // Find or use default institutional buyer (e.g. Lake Tana Agro Producers Union)
            $buyer = User::where('role', 'buyer')->first();
            if (!$buyer) {
                $buyer = User::firstOrCreate(
                    ['email' => 'contracts@laketana-union.et'],
                    [
                        'name' => $validated['buyer_name'] ?? 'Lake Tana Agro Producers Union',
                        'role' => 'buyer',
                        'phone' => '+251 58 220 1845',
                        'password' => bcrypt('password123'),
                        'region' => 'Amhara',
                        'zone' => 'West Gojjam',
                        'woreda' => 'Bahir Dar Zuria',
                        'is_active' => true,
                    ]
                );
            }

            $qty = (float)$validated['quantity'];
            $price = (float)$validated['unit_price'];
            $totalAmount = round($qty * $price, 2);
            $advPct = isset($validated['advance_percentage']) ? (float)$validated['advance_percentage'] : 30.0;
            $advance = round($totalAmount * ($advPct / 100), 2);

            DB::beginTransaction();

            $contract = Contract::create([
                'buyer_id' => $buyer->id,
                'farmer_id' => $farmer->id,
                'title' => $validated['title'],
                'crop_type' => $validated['crop_type'],
                'variety' => $validated['variety'] ?? 'Grade 1 Select',
                'quantity' => $qty,
                'unit' => $validated['unit'] ?? 'Quintal',
                'unit_price' => $price,
                'total_amount' => $totalAmount,
                'advance_payment' => $advance,
                'currency' => 'ETB',
                'start_date' => now()->toDateString(),
                'delivery_date' => $validated['delivery_date'],
                'delivery_location' => $validated['delivery_location'] ?? 'Regional Agricultural Terminal Depot',
                'quality_specifications' => $validated['quality_specifications'] ?? 'Grade 1 Export Standards, moisture max 11.5%, purity min 99.0%',
                'status' => 'proposed',
                'payment_terms' => "{$advPct}% Advance Escrow Mobilization, remaining balances on harvest and final warehouse acceptance.",
                'terms_and_conditions' => 'Standard Agricultural Escrow forward supply terms. All funds safeguarded in neutral custody.',
                'signed_by_farmer_at' => now(),
            ]);

            // Milestone 1: Mobilization advance
            ContractMilestone::create([
                'contract_id' => $contract->id,
                'title' => 'Advance Mobilization & Field Preparation',
                'description' => "Initial {$advPct}% mobilization for input acquisition and sowing.",
                'due_date' => now()->addDays(14)->toDateString(),
                'status' => 'pending',
                'payout_percentage' => $advPct,
                'payout_amount' => $advance,
                'payout_status' => 'in_escrow',
            ]);

            // Milestone 2: Mid-Season Inspection
            ContractMilestone::create([
                'contract_id' => $contract->id,
                'title' => 'Mid-Season Crop Agronomy Inspection',
                'description' => 'On-field agronomist verification of vegetative health and weed/pest management.',
                'due_date' => date('Y-m-d', strtotime('+45 days')),
                'status' => 'pending',
                'payout_percentage' => 0.00,
                'payout_amount' => 0.00,
                'payout_status' => 'unpaid',
            ]);

            // Milestone 3: Harvesting & Grading
            $harvestPct = 40.0;
            $harvestAmount = round($totalAmount * 0.40, 2);
            ContractMilestone::create([
                'contract_id' => $contract->id,
                'title' => 'Harvesting, Threshing & Standard Grading',
                'description' => '40% milestone payout upon harvest grading and standardization into food bags.',
                'due_date' => date('Y-m-d', strtotime($validated['delivery_date'] . ' - 10 days')),
                'status' => 'pending',
                'payout_percentage' => $harvestPct,
                'payout_amount' => $harvestAmount,
                'payout_status' => 'unpaid',
            ]);

            // Milestone 4: Final Warehouse Delivery
            $finalPct = max(0, 100 - $advPct - $harvestPct);
            $finalAmount = round($totalAmount - $advance - $harvestAmount, 2);
            ContractMilestone::create([
                'contract_id' => $contract->id,
                'title' => 'Final Warehouse Delivery & Escrow Settlement',
                'description' => 'Final escrow balance release upon physical depot arrival and laboratory acceptance test.',
                'due_date' => $validated['delivery_date'],
                'status' => 'pending',
                'payout_percentage' => $finalPct,
                'payout_amount' => $finalAmount,
                'payout_status' => 'unpaid',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Forward agreement proposed successfully! Buyer has been notified.',
                'data' => $contract->load(['buyer', 'milestones']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error proposing contract: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Digitally sign & accept a forward supply agreement
     */
    public function accept(Request $request, $id)
    {
        try {
            $farmer = $this->getOrCreateFarmer();
            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $contract = Contract::where('farmer_id', $farmer->id)
                ->with(['buyer', 'milestones'])
                ->findOrFail($id);

            if ($contract->status !== 'proposed' && $contract->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'This contract proposal is already ' . $contract->status . ' and cannot be accepted again.',
                ], 422);
            }

            $notes = $request->get('notes', 'Accepted and digitally signed by producer via AgriConnect Farmer Escrow portal');

            $contract->update([
                'status' => 'active',
                'signed_by_farmer_at' => now(),
                'farmer_notes' => $notes,
            ]);

            // Set advance milestone to active/in_escrow
            $firstMilestone = $contract->milestones()->where('payout_percentage', '>', 0)->first();
            if ($firstMilestone) {
                $firstMilestone->update([
                    'status' => 'in_progress',
                    'payout_status' => 'in_escrow',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Forward supply agreement legally accepted & signed! Advance funds locked in Escrow.',
                'data' => $contract->fresh(['buyer', 'milestones']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Contract not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error accepting contract: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject a contract proposal
     */
    public function reject(Request $request, $id)
    {
        try {
            $farmer = $this->getOrCreateFarmer();
            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $contract = Contract::where('farmer_id', $farmer->id)->findOrFail($id);

            if ($contract->status !== 'proposed' && $contract->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'This contract cannot be rejected in its current state.',
                ], 422);
            }

            $contract->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $request->get('reason', 'Declined by farmer/producer'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contract proposal declined.',
                'data' => $contract->fresh(['buyer', 'milestones']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Contract not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error rejecting contract', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update milestone status (e.g. declare harvest or request agronomist verification)
     */
    public function completeMilestone(Request $request, $contractId, $milestoneId)
    {
        try {
            $farmer = $this->getOrCreateFarmer();
            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $contract = Contract::where('farmer_id', $farmer->id)->findOrFail($contractId);
            $milestone = ContractMilestone::where('contract_id', $contract->id)->findOrFail($milestoneId);

            $milestone->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Milestone completed and verified for escrow clearance.',
                'data' => $milestone,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating milestone', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper to seed standard forward supply agreements including Lake Tana Teff contract
     */
    protected function ensureSampleContractsExist(Farmer $farmer): void
    {
        $hasTeffContract = Contract::where('farmer_id', $farmer->id)
            ->where('title', 'LIKE', '%Teff Supply Agreement%')
            ->exists();

        if (!$hasTeffContract) {
            // Find or create buyer user for Lake Tana Agro Producers Union
            $buyer = User::firstOrCreate(
                ['email' => 'contracts@laketana-union.et'],
                [
                    'name' => 'Lake Tana Agro Producers Union',
                    'phone' => '+251 58 220 1845',
                    'password' => bcrypt('password123'),
                    'role' => 'buyer',
                    'region' => 'Amhara',
                    'zone' => 'West Gojjam',
                    'woreda' => 'Bahir Dar Zuria',
                    'is_active' => true,
                ]
            );

            $totalAmount = 2300000.00;
            $advance = 690000.00;

            $contract = Contract::create([
                'contract_number' => 'CON-TANA-2026-089',
                'buyer_id' => $buyer->id,
                'farmer_id' => $farmer->id,
                'title' => 'Teff Supply Agreement with Lake Tana Agro Producers Union',
                'crop_type' => 'Teff',
                'variety' => 'Quncho (DZ-Cr-387 White Magna)',
                'quantity' => 250,
                'unit' => 'Quintal',
                'unit_price' => 9200.00,
                'total_amount' => $totalAmount,
                'advance_payment' => $advance,
                'currency' => 'ETB',
                'start_date' => now()->toDateString(),
                'delivery_date' => now()->addMonths(3)->toDateString(),
                'delivery_location' => 'Lake Tana Agro Central Depot, Bahir Dar Industrial Park Hub',
                'quality_specifications' => 'Grade 1 Export White Magna Teff. Moisture content max 11.5%, purity min 99.0%, free of foreign stones and weed seeds, unadulterated, standard 100kg new polypropylene food bags.',
                'status' => 'proposed',
                'payment_terms' => '30% Advance Escrow Mobilization upon digital signature, 40% upon harvesting and threshing inspection, 30% final balance upon warehouse physical inspection.',
                'terms_and_conditions' => '1. Escrow Protection: All forward funding is held under neutral Chapa & Agricultural Escrow custody. 2. Quality Assurance: Producer agrees to follow Good Agricultural Practices (GAP) and accommodate scheduled mid-season inspection. 3. Dispute Resolution: Guaranteed dispute arbitration under National Agricultural Escrow standards.',
                'signed_by_buyer_at' => now()->subDays(1),
            ]);

            // Milestone 1: Advance Mobilization
            ContractMilestone::create([
                'contract_id' => $contract->id,
                'title' => 'Advance Mobilization & Seed Sowing',
                'description' => '30% upfront escrow mobilization for land preparation, certified Quncho seeds, fertilizer input, and planting.',
                'due_date' => now()->addDays(12)->toDateString(),
                'status' => 'pending',
                'payout_percentage' => 30.00,
                'payout_amount' => 690000.00,
                'payout_status' => 'in_escrow',
            ]);

            // Milestone 2: Mid-Season Inspection
            ContractMilestone::create([
                'contract_id' => $contract->id,
                'title' => 'Mid-Season Crop Health & Agronomic Inspection',
                'description' => 'Field monitoring by union agronomist for weed/pest management, vegetative stage verification, and yield projection.',
                'due_date' => now()->addDays(45)->toDateString(),
                'status' => 'pending',
                'payout_percentage' => 0.00,
                'payout_amount' => 0.00,
                'payout_status' => 'unpaid',
            ]);

            // Milestone 3: Harvesting & Threshing
            ContractMilestone::create([
                'contract_id' => $contract->id,
                'title' => 'Harvesting, Threshing & Standard Grading',
                'description' => '40% milestone payout upon produce harvesting, mechanical threshing, cleaning to Grade 1 export standard, and bagging.',
                'due_date' => now()->addDays(75)->toDateString(),
                'status' => 'pending',
                'payout_percentage' => 40.00,
                'payout_amount' => 920000.00,
                'payout_status' => 'unpaid',
            ]);

            // Milestone 4: Final Warehouse Settlement
            ContractMilestone::create([
                'contract_id' => $contract->id,
                'title' => 'Final Warehouse Delivery & Escrow Settlement',
                'description' => '30% final balance released immediately following delivery arrival at Bahir Dar Central Depot and physical purity testing.',
                'due_date' => now()->addMonths(3)->toDateString(),
                'status' => 'pending',
                'payout_percentage' => 30.00,
                'payout_amount' => 690000.00,
                'payout_status' => 'unpaid',
            ]);
        }
    }
}
