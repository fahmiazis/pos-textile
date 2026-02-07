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
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name'
        ]);

        return Role::create([
            'name' => $data['name'],
            'guard_name' => 'web'
        ]);
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'superadmin') {
            return response()->json([
                'message' => 'Superadmin role cannot be modified'
            ], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id
        ]);

        $role->update($data);

        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'superadmin') {
            return response()->json([
                'message' => 'Superadmin role cannot be deleted'
            ], 403);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted']);
    }

    public function syncPermissions(Request $request, Role $role)
    {
        if ($role->name === 'superadmin') {
            return response()->json([
                'message' => 'Superadmin permissions are implicit'
            ]);
        }

        $data = $request->validate([
            'permissions' => 'required|array'
        ]);

        $role->syncPermissions($data['permissions']);

        return response()->json([
            'message' => 'Permissions updated',
            'permissions' => $role->getPermissionNames()
        ]);
    }
}