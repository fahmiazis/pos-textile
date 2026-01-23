<?php

namespace App\Support;

use Illuminate\Support\Collection;

class AbilityFormatter
{
  public static function fromPermissions(Collection $permissions): array
  {
    $abilities = [];

    foreach ($permissions as $permission) {
      if (!str_contains($permission, '.')) {
        continue;
      }

      [$module, $action] = explode('.', $permission, 2);

      $abilities[$module][] = $action;
    }

    // buang duplikat index
    foreach ($abilities as $module => $actions) {
      $abilities[$module] = array_values(array_unique($actions));
    }

    return $abilities;
  }
}
