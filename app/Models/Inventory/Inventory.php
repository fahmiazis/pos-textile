<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
  use HasFactory;

  protected $table = 'inventories';

  protected $fillable = [
    'store_id',
    'product_id',
    'stock_on_hand',
    'stock_reserved',
    'stock_available',
  ];

  /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

  public function movements()
  {
    return $this->hasMany(InventoryMovement::class);
  }

  public function store()
  {
    return $this->belongsTo(\App\Models\Master\Store::class);
  }

  public function product()
  {
    return $this->belongsTo(\App\Models\Master\Product::class);
  }
}