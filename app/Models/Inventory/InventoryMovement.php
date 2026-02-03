<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryMovement extends Model
{
  use HasFactory;

  protected $table = 'inventory_movements';

  protected $fillable = [
    'inventory_id',
    'type',
    'qty',
    'reference_type',
    'reference_id',
    'notes',
  ];

  /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

  public function inventory()
  {
    return $this->belongsTo(Inventory::class);
  }
}