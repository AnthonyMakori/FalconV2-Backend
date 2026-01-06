<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * List all permissions along with roles
     * Used to populate the permission matrix
     */
    public function index()
    {
        // Eager load roles for each permission
        $permissions = Permission::with('roles')->get();

        // Fetch all roles
        $roles = Role::all();

        return response()->json([
            'permissions' => $permissions,
            'roles' => $roles,
        ]);
    }

    /**
     * Create a new permission
     * Optionally assign it to roles
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'roles' => 'array' // optional array of role IDs
        ]);

        $permission = Permission::create($request->only('name', 'description', 'category'));

        // Assign to roles if provided
        if ($request->has('roles')) {
            $permission->roles()->sync($request->roles);
        }

        return response()->json($permission, 201);
    }

    /**
     * Update permissions assigned to a role
     * This is used when toggling checkboxes in the permission matrix
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Sync the role with the new permissions
        $role->permissions()->sync($request->permissions);

        return response()->json([
            'message' => 'Permissions updated successfully',
            'role' => $role->load('permissions'),
        ]);
    }
}
