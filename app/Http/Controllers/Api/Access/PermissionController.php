<?php

namespace App\Http\Controllers\Api\Access;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return Permission::orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id'   => $p->id,
                'name' => $p->name,
            ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create([
            'name'       => $data['name'],
            'guard_name' => 'web',
        ]);

        return response()->json([
            'message' => 'Permission created',
            'data'    => [
                'id'   => $permission->id,
                'name' => $permission->name,
            ],
        ], 201);
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update($data);

        return response()->json([
            'message' => 'Permission updated',
            'data'    => [
                'id'   => $permission->id,
                'name' => $permission->name,
            ],
        ]);
    }

    public function destroy(Permission $permission)
    {
        $rolesCount = $permission->roles()->count();
        $usersCount = $permission->users()->count();

        if ($rolesCount > 0 || $usersCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Permission cannot be deleted',
                'reason'  => 'Permission is still in use',
                'details' => [
                    'permission'  => $permission->name,
                    'roles_count' => $rolesCount,
                    'users_count' => $usersCount,
                ],
                'hint' => 'Unassign this permission from all roles/users before deleting it',
            ], 422);
        }

        $permissionName = $permission->name;
        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully',
            'data' => [
                'permission' => $permissionName,
            ]
        ]);
    }
}