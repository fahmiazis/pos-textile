<?php

namespace App\Http\Controllers\Api\Access;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Access\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        protected UserService $service
    ) {}

    /**
     * List users
     */
    public function index()
    {
        return User::with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    /**
     * Create new user
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:6',
            'roles'     => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $user = $this->service->create($data);

        return response()->json([
            'message' => 'User created',
            'data'    => $this->formatUser($user),
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],

            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'password'  => ['nullable', 'min:6'],
            'roles'     => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = $this->service->update($user, $data);

        return response()->json([
            'message' => 'User updated',
            'data'    => $this->formatUser($user),
        ]);
    }


    /**
     * Centralized response formatter
     */
    private function formatUser(User $user): array
    {
        $user->load('roles');

        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'is_active'  => $user->is_active,
            'roles'      => $user->getRoleNames(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }


    /**
     * Assign roles to user
     */
    public function syncRoles(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => ['required', 'array'],
        ]);

        $user->syncRoles($data['roles']);

        $user->refresh()->load('roles');

        return response()->json([
            'message' => 'Roles updated',
            'data'    => $this->formatUser($user),
        ]);
    }


    /**
     * Toggle active / inactive user
     */
    public function toggleActive(Request $request, User $user)
    {
        $data = $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $this->service->toggleActive($user, $data['is_active']);

        $user->refresh()->load('roles');

        return response()->json([
            'message' => $data['is_active']
                ? 'User activated'
                : 'User deactivated',
            'data' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'is_active'  => $user->is_active,
                'roles'      => $user->getRoleNames(),
                'updated_at' => $user->updated_at,
            ]
        ]);
    }


    /**
     * Reset user password (admin action)
     */
    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => 'required|min:6'
        ]);

        $this->service->resetPassword($user, $data['password']);

        return response()->json([
            'message' => 'Password reset successfully',
            'user' => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'is_active' => $user->is_active,
                'roles'     => $user->getRoleNames(),
            ]
        ]);
    }


    /**
     * Delete user (soft or hard)
     */
    public function destroy(User $user)
    {
        // optional: protect superadmin
        if ($user->hasRole('superadmin')) {
            return response()->json([
                'message' => 'Superadmin cannot be deleted'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted'
        ]);
    }
    /**
     * Get user effective permissions
     * (direct + via roles)
     */
    public function permissions(User $user)
    {
        return response()->json([
            'data' => [
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                ],
                'permissions' => [
                    'direct'     => $user->getDirectPermissions()->pluck('name'),
                    'via_roles'  => $user->getPermissionsViaRoles()->pluck('name'),
                    'effective'  => $user->getAllPermissions()->pluck('name'),
                ],
            ]
        ]);
    }
}