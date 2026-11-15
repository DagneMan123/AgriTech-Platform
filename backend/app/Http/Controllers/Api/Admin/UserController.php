<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Get all users with filters, role categorization and partner verification status
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'documents', 'supplier', 'transporter', 'cooperative']);

        // Filter by role
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Filter by status (active, pending_approval, suspended)
        if ($request->filled('status') && $request->status !== 'all') {
            $status = strtolower($request->status);
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'pending' || $status === 'pending_approval') {
                $query->where('is_active', false)
                      ->whereIn('role', ['supplier', 'transport', 'cooperative']);
            } elseif ($status === 'suspended' || $status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search by keyword
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        $limit = (int) $request->get('limit', 20);
        $users = $query->latest()->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Create a new user / Direct Admin Creation (Workflow 3: Finance Manager, Agricultural Expert)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,farmer,buyer,supplier,transport,cooperative,expert,financial',
            'region' => 'nullable|string|max:100',
        ]);

        $role = strtolower($validated['role']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'region' => $validated['region'] ?? 'Addis Ababa',
            'address' => $validated['region'] ?? 'Addis Ababa',
            'location' => $validated['region'] ?? 'Addis Ababa',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role);

        // Auto-provision profile for created role
        try {
            if ($role === 'farmer') {
                \App\Models\Farmer::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'farm_name' => $user->name . ' Farm',
                        'region' => $user->region,
                        'verification_status' => 'verified',
                    ]
                );
            } elseif ($role === 'buyer') {
                \App\Models\Buyer::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'business_name' => $user->name . ' Trading',
                        'verification_status' => 'verified',
                    ]
                );
            } elseif ($role === 'expert') {
                if (class_exists(\App\Models\Expert::class)) {
                    \App\Models\Expert::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'name' => $user->name,
                            'specialization' => 'Agronomy, Crop & Soil Health',
                            'is_available' => true,
                        ]
                    );
                }
            } elseif ($role === 'supplier' && class_exists(\App\Models\Supplier::class)) {
                \App\Models\Supplier::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'company_name' => $user->name . ' Supplies',
                        'verification_status' => 'verified',
                    ]
                );
            } elseif ($role === 'transport' && class_exists(\App\Models\Transporter::class)) {
                \App\Models\Transporter::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'company_name' => $user->name . ' Transport Fleet',
                        'is_verified' => true,
                    ]
                );
            }
        } catch (\Throwable $e) {
            \Log::warning("Admin create user profile provisioning skipped: {$e->getMessage()}");
        }

        return response()->json([
            'success' => true,
            'message' => "Internal user {$user->name} ({$role}) created and activated successfully.",
            'data' => $user->load('roles', 'documents'),
        ], 201);
    }

    /**
     * Get user by ID
     */
    public function show($id)
    {
        $user = User::with(['roles', 'documents', 'supplier', 'transporter', 'cooperative'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update user details
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string',
            'region' => 'sometimes|string|max:100',
            'role' => 'sometimes|string|in:admin,farmer,buyer,supplier,transport,cooperative,expert,financial',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->load('roles', 'documents'),
        ]);
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Approve partner account (Workflow 2: Partner Request & Approval)
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_active' => true,
            'email_verified_at' => $user->email_verified_at ?: now(),
        ]);

        // Mark associated trade documents as verified
        UserDocument::where('user_id', $user->id)
            ->where('verification_status', 'pending')
            ->update([
                'verification_status' => 'verified',
                'verified_at' => now(),
                'verified_by' => auth()->id() ?? 1,
            ]);

        // Activate partner profile
        try {
            if ($user->role === 'supplier' && class_exists(\App\Models\Supplier::class)) {
                \App\Models\Supplier::where('user_id', $user->id)->update(['verification_status' => 'verified']);
            } elseif ($user->role === 'transport' && class_exists(\App\Models\Transporter::class)) {
                \App\Models\Transporter::where('user_id', $user->id)->update(['is_verified' => true]);
            } elseif ($user->role === 'cooperative' && class_exists(\App\Models\Cooperative::class)) {
                \App\Models\Cooperative::where('user_id', $user->id)->update(['is_active' => true]);
            }
        } catch (\Throwable $pe) {
            \Log::warning("Partner profile approval status update skipped: {$pe->getMessage()}");
        }

        return response()->json([
            'success' => true,
            'message' => "Partner account for {$user->name} ({$user->role}) has been approved and activated. They can now log in.",
            'data' => $user->load('roles', 'documents', 'supplier', 'transporter', 'cooperative'),
        ]);
    }

    /**
     * Suspend user account
     */
    public function suspend(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => "User {$user->name} has been suspended/deactivated.",
            'data' => $user->load('roles', 'documents', 'supplier', 'transporter', 'cooperative'),
        ]);
    }

    /**
     * Get user statistics across roles and approval states
     */
    public function statistics()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'pending_partners' => User::where('is_active', false)
                ->whereIn('role', ['supplier', 'transport', 'cooperative'])
                ->count(),
            'suspended_users' => User::where('is_active', false)->count(),
            'by_role' => [
                'farmer' => User::where('role', 'farmer')->count(),
                'buyer' => User::where('role', 'buyer')->count(),
                'supplier' => User::where('role', 'supplier')->count(),
                'transport' => User::where('role', 'transport')->count(),
                'cooperative' => User::where('role', 'cooperative')->count(),
                'expert' => User::where('role', 'expert')->count(),
                'financial' => User::where('role', 'financial')->count(),
                'admin' => User::where('role', 'admin')->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
