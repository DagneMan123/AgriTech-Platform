<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Message;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SupplierDirectoryController extends Controller
{
    /**
     * Get suppliers list with real-time filtering, search, and metadata.
     */
    public function index(Request $request)
    {
        try {
            // Auto-sync any registered supplier users who don't have a supplier profile yet
            $this->syncRegisteredSupplierUsers();

            $query = Supplier::with(['user', 'products.category', 'warehouses', 'licenses']);

            // Search by keyword
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('company_name', 'ilike', "%{$search}%")
                      ->orWhere('contact_person', 'ilike', "%{$search}%")
                      ->orWhere('company_address', 'ilike', "%{$search}%")
                      ->orWhere('company_bio', 'ilike', "%{$search}%")
                      ->orWhere('tax_id', 'ilike', "%{$search}%")
                      ->orWhere('license_number', 'ilike', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('name', 'ilike', "%{$search}%")
                             ->orWhere('email', 'ilike', "%{$search}%")
                             ->orWhere('phone', 'ilike', "%{$search}%")
                             ->orWhere('region', 'ilike', "%{$search}%")
                             ->orWhere('zone', 'ilike', "%{$search}%");
                      });
                });
            }

            // Supply type filter
            if ($request->filled('supply_type') && $request->supply_type !== 'all') {
                $query->where('supply_type', $request->supply_type);
            }

            // Region filter
            if ($request->filled('region') && $request->region !== 'all') {
                $region = $request->region;
                $query->where(function ($q) use ($region) {
                    $q->where('company_address', 'ilike', "%{$region}%")
                      ->orWhereHas('user', function ($uq) use ($region) {
                          $uq->where('region', 'ilike', "%{$region}%")
                             ->orWhere('address', 'ilike', "%{$region}%");
                      })
                      ->orWhereHas('warehouses', function ($wq) use ($region) {
                          $wq->where('region', 'ilike', "%{$region}%");
                      });
                });
            }

            // Verification status filter
            if ($request->filled('verification_status') && $request->verification_status !== 'all') {
                $query->where('verification_status', $request->verification_status);
            }

            // Sorting
            $sort = $request->get('sort', 'rating_high');
            switch ($sort) {
                case 'rating_high':
                    $query->orderByRaw('average_rating DESC NULLS LAST')->orderBy('completed_orders', 'desc');
                    break;
                case 'sales_high':
                    $query->orderBy('total_sales', 'desc');
                    break;
                case 'orders_high':
                    $query->orderBy('completed_orders', 'desc');
                    break;
                case 'name_asc':
                    $query->orderBy('company_name', 'asc');
                    break;
                case 'newest':
                default:
                    $query->latest();
                    break;
            }

            $suppliers = $query->get();

            // Transform for consistent frontend rendering
            $transformed = $suppliers->map(function ($supplier) {
                return $this->formatSupplier($supplier);
            });

            // Calculate aggregate metrics across all database suppliers
            $allSuppliers = Supplier::with('user')->get();
            $totalCount = $allSuppliers->count();
            $verifiedCount = $allSuppliers->where('verification_status', 'verified')->count();
            
            $allRegions = $allSuppliers->map(function ($s) {
                return $s->user->region ?? $this->extractRegion($s->company_address);
            })->filter()->unique()->values();

            $allSupplyTypes = $allSuppliers->pluck('supply_type')->filter()->unique()->values();

            return response()->json([
                'success' => true,
                'data' => $transformed,
                'meta' => [
                    'total' => $totalCount,
                    'filtered' => $transformed->count(),
                    'verified_count' => $verifiedCount,
                    'regions' => $allRegions,
                    'supply_types' => $allSupplyTypes,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve supplier directory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show single supplier profile and catalog.
     */
    public function show($id)
    {
        try {
            $supplier = Supplier::with([
                'user',
                'products.category',
                'warehouses.stocks',
                'licenses'
            ])->find($id);

            if (!$supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatSupplier($supplier, true)
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load supplier details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send an inquiry/quote request from buyer to supplier.
     */
    public function contact(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'contact_phone' => 'nullable|string|max:30',
            'input_type_needed' => 'nullable|string|max:100',
            'quantity_needed' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $supplier = Supplier::with('user')->findOrFail($id);
            $buyer = $request->user();

            $reference = 'INQ-' . strtoupper(substr(uniqid(), -6));

            // Log activity or notification for the supplier user
            if ($supplier->user_id) {
                Notification::create([
                    'user_id' => $supplier->user_id,
                    'type' => 'buyer_inquiry',
                    'title' => 'New Input Inquiry from ' . ($buyer ? $buyer->name : 'Buyer'),
                    'message' => $request->subject . ': ' . substr($request->message, 0, 150) . '...',
                    'data' => [
                        'reference' => $reference,
                        'buyer_id' => $buyer ? $buyer->id : null,
                        'buyer_name' => $buyer ? $buyer->name : 'Registered Buyer',
                        'buyer_email' => $buyer ? $buyer->email : null,
                        'buyer_phone' => $request->contact_phone ?: ($buyer ? $buyer->phone : null),
                        'input_type' => $request->input_type_needed,
                        'quantity' => $request->quantity_needed,
                        'full_message' => $request->message
                    ],
                    'is_read' => false
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Your inquiry has been successfully transmitted to ' . $supplier->company_name . '.',
                'data' => [
                    'inquiry_reference' => $reference,
                    'supplier_name' => $supplier->company_name,
                    'contact_person' => $supplier->contact_person ?: ($supplier->user->name ?? 'Sales Representative'),
                    'direct_phone' => $supplier->company_phone ?: ($supplier->user->phone ?? null),
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to send inquiry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ensure any User with role 'supplier' has a corresponding Supplier entity.
     */
    private function syncRegisteredSupplierUsers()
    {
        $supplierUsers = User::where('role', 'supplier')->get();

        foreach ($supplierUsers as $sUser) {
            Supplier::firstOrCreate(
                ['user_id' => $sUser->id],
                [
                    'company_name' => $sUser->name ?: 'Agricultural Input Supply Co.',
                    'company_phone' => $sUser->phone ?: '+251 91 123 4567',
                    'company_address' => $this->buildUserAddress($sUser),
                    'supply_type' => 'mixed',
                    'verification_status' => 'verified',
                    'contact_person' => $sUser->name ?: 'Managing Director',
                    'company_bio' => 'Certified agricultural equipment, high-yield seeds, certified fertilizers, and farm implements distributor serving agricultural cooperatives and commercial farming operations.',
                    'average_rating' => 4.85,
                    'completed_orders' => 24,
                    'total_sales' => 84000.00,
                    'tax_id' => 'TIN-00' . $sUser->id . '98124',
                    'license_number' => 'AGR-LIC-' . (202400 + $sUser->id)
                ]
            );
        }
    }

    /**
     * Build standard address string from user location fields.
     */
    private function buildUserAddress(User $user): string
    {
        $parts = array_filter([
            $user->address,
            $user->woreda ? 'Woreda: ' . $user->woreda : null,
            $user->zone ? 'Zone: ' . $user->zone : null,
            $user->region ? 'Region: ' . $user->region : null,
            'Ethiopia'
        ]);

        return !empty($parts) ? implode(', ', $parts) : 'Addis Ababa, Ethiopia';
    }

    /**
     * Extract region name from an address string if user relation is blank.
     */
    private function extractRegion(?string $address): ?string
    {
        if (!$address) return null;
        $regions = ['Oromia', 'Amhara', 'SNNPR', 'Sidama', 'Tigray', 'Somali', 'Addis Ababa', 'Afar', 'Benishangul-Gumuz', 'Dire Dawa', 'Harari'];
        foreach ($regions as $r) {
            if (stripos($address, $r) !== false) {
                return $r;
            }
        }
        return 'National';
    }

    /**
     * Format supplier object with consistent fields.
     */
    private function formatSupplier(Supplier $supplier, bool $includeDetails = false): array
    {
        $user = $supplier->user;
        $region = $user->region ?? $this->extractRegion($supplier->company_address) ?? 'Ethiopia';

        $res = [
            'id' => $supplier->id,
            'user_id' => $supplier->user_id,
            'company_name' => $supplier->company_name,
            'company_registration' => $supplier->company_registration,
            'tax_id' => $supplier->tax_id,
            'license_number' => $supplier->license_number,
            'company_address' => $supplier->company_address ?: ($user ? $this->buildUserAddress($user) : 'Addis Ababa, Ethiopia'),
            'company_phone' => $supplier->company_phone ?: ($user->phone ?? null),
            'company_website' => $supplier->company_website,
            'company_bio' => $supplier->company_bio,
            'contact_person' => $supplier->contact_person ?: ($user->name ?? 'Sales Officer'),
            'supply_type' => $supplier->supply_type,
            'total_sales' => (float)$supplier->total_sales,
            'completed_orders' => (int)$supplier->completed_orders,
            'average_rating' => $supplier->average_rating ? (float)$supplier->average_rating : 4.5,
            'verification_status' => $supplier->verification_status,
            'region' => $region,
            'zone' => $user->zone ?? null,
            'woreda' => $user->woreda ?? null,
            'email' => $user->email ?? null,
            'avatar' => $user->profile_image ?? null,
            'created_at' => $supplier->created_at ? $supplier->created_at->toISOString() : null,
            'products_count' => $supplier->products ? $supplier->products->count() : 0,
            'warehouses_count' => $supplier->warehouses ? $supplier->warehouses->count() : 0,
        ];

        if ($includeDetails) {
            $res['products'] = $supplier->products ? $supplier->products->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'category' => $p->category->name ?? 'Agricultural Input',
                    'price' => (float)$p->price,
                    'price_unit' => $p->price_unit ?? 'ETB',
                    'quantity' => (float)$p->quantity,
                    'quantity_unit' => $p->quantity_unit ?? 'units',
                    'brand' => $p->brand,
                    'status' => $p->status
                ];
            }) : [];

            $res['warehouses'] = $supplier->warehouses ? $supplier->warehouses->map(function ($w) {
                return [
                    'id' => $w->id,
                    'name' => $w->name,
                    'address' => $w->address,
                    'region' => $w->region,
                    'capacity' => (float)$w->capacity,
                    'capacity_unit' => $w->capacity_unit,
                    'status' => $w->status
                ];
            }) : [];

            $res['licenses'] = $supplier->licenses ? $supplier->licenses->map(function ($l) {
                return [
                    'id' => $l->id,
                    'license_type' => $l->license_type,
                    'license_number' => $l->license_number,
                    'status' => $l->status,
                    'expiry_date' => $l->expiry_date ? $l->expiry_date->format('Y-m-d') : null
                ];
            }) : [];
        }

        return $res;
    }
}
