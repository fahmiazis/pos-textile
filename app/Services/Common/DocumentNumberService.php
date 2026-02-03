<?php

namespace App\Services\Common;

use Illuminate\Support\Facades\DB;

class DocumentNumberService
{

  public static function generate(
    string $table,
    string $column,
    string $prefix
  ): string {
    $year  = now()->format('y');
    $month = now()->format('m');

    return DB::transaction(function () use ($table, $column, $prefix, $year, $month) {

      $lastNumber = DB::table($table)
        ->where($column, 'like', "{$prefix}{$year}{$month}%")
        ->lockForUpdate()
        ->orderByDesc($column)
        ->value($column);

      $sequence = $lastNumber
        ? ((int) substr($lastNumber, -4)) + 1
        : 1;

      return sprintf(
        '%s%s%s%04d',
        $prefix,
        $year,
        $month,
        $sequence
      );
    });
  }
}
