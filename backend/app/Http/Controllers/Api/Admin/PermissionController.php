<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Get all permissions
     */
    public function index(Request $request)
    {
        $query = Permission::query();

        if ($request->has('module')) {
            $query->where('module', $request->module);
        }

        $permissions = $query->paginate($request->get('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
    }

    /**
     * Create a new permission
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions',
            'description' => 'sometimes|string',
            'module' => 'required|string',
        ]);

        $permission = Permission::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Permission created successfully',
            'data' => $permission,
        ], 201);
    }

    /**
     * Get permission by ID
     */
    public function show(Permission $permission)
    {
        return response()->json([
            'success' => true,
            'data' => $permission,
        ]);
    }

    /**
     * Update permission
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:permissions,name,' . $permission->id,
            'description' => 'sometimes|string',
            'module' => 'sometimes|string',
        ]);

        $permission->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Permission updated successfully',
            'data' => $permission,
        ]);
    }

    /**
     * Delete permission
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully',
        ]);
    }

    /**
     * Get permissions by module
     */
    public function byModule(string $module)
    {
        $permissions = Permission::where('module', $module)->get();

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
    }
}
