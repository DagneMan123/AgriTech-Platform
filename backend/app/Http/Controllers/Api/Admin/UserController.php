<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Get all users with filters and pagination
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', $request->role);
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        $users = $query->with('roles')->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Create a new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'phone' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        $user->assignRole($validated['role']);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user->load('roles'),
        ], 201);
    }

    /**
     * Get user by ID
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user->load('roles', 'permissions'),
        ]);
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string',
            'status' => 'sometimes|in:active,inactive,suspended',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user,
        ]);
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Approve user
     */
    public function approve(User $user)
    {
        $user->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'User approved successfully',
            'data' => $user,
        ]);
    }

    /**
     * Suspend user
     */
    public function suspend(Request $request, User $user)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $user->update([
            'status' => 'suspended',
            'suspension_reason' => $validated['reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User suspended successfully',
            'data' => $user,
        ]);
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user->syncRoles($validated['role']);

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully',
            'data' => $user->load('roles'),
        ]);
    }

    /**
     * Get user statistics
     */
    public function statistics()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            'users_by_role' => User::with('roles')
                ->get()
                ->groupBy(fn ($user) => $user->roles->first()?->name ?? 'No Role')
                ->map(fn ($group) => $group->count()),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
