<?php

namespace App\Http\Controllers\Api\Access;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return Role::with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn($role) => [
                'id'          => $role->id,
                'name'        => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        $role = Role::create([
            'name'       => $data['name'],
            'guard_name' => 'web',
        ]);

        return response()->json([
            'message' => 'Role created',
            'data'    => [
                'id'   => $role->id,
                'name' => $role->name,
            ],
        ], 201);
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'superadmin') {
            return response()->json([
                'message' => 'Superadmin role cannot be modified'
            ], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        $role->update($data);

        return response()->json([
            'message' => 'Role updated',
            'data'    => [
                'id'   => $role->id,
                'name' => $role->name,
            ],
        ]);
    }

    public function destroy(Role $role)
    {
        $usersCount = $role->users()->count();

        if ($usersCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Role cannot be deleted',
                'reason'  => 'Role is still assigned to users',
                'details' => [
                    'role'        => $role->name,
                    'users_count' => $usersCount,
                ],
                'hint' => 'Remove this role from all users before deleting it',
            ], 422);
        }

        $roleName = $role->name;
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully',
            'data' => [
                'role' => $roleName,
            ]
        ]);
    }

    /**
     * Replace permissions of role (as-is → to-be)
     */
    public function syncPermissions(Request $request, Role $role)
    {
        if ($role->name === 'superadmin') {
            return response()->json([
                'message' => 'Superadmin permissions are implicit'
            ]);
        }

        $data = $request->validate([
            'permissions' => 'required|array',
        ]);

        $before = $role->getPermissionNames()->values();

        $role->syncPermissions($data['permissions']);

        return response()->json([
            'message' => 'Permissions updated',
            'data' => [
                'role' => $role->name,
                'before' => $before,
                'after'  => $role->getPermissionNames(),
            ]
        ]);
    }
}