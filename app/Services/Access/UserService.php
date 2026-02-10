<?php

namespace App\Services\Access;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
  public function create(array $data): User
  {
    $user = User::create([
      'name'      => $data['name'],
      'email'     => $data['email'],
      'password'  => Hash::make($data['password']),
      'is_active' => true,
    ]);

    if (!empty($data['roles'])) {
      $user->syncRoles($data['roles']);
    }

    return $user;
  }

  public function update(User $user, array $data): User
  {
    // Handle password
    if (array_key_exists('password', $data)) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }
    }

    $user->update($data);

    if (!empty($data['roles'])) {
      $user->syncRoles($data['roles']);
    }

    return $user->refresh()->load('roles');
  }



  public function toggleActive(User $user, bool $status): User
  {
    $user->update(['is_active' => $status]);
    return $user;
  }

  public function resetPassword(User $user, string $password): void
  {
    $user->update([
      'password' => Hash::make($password)
    ]);
  }
}