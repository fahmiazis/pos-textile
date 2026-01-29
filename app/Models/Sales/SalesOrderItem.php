<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesOrderItem extends Model
{
  use HasFactory;

  protected $table = 'sales_order_items';

  protected $fillable = [
    'sales_order_id',
    'product_id',
    'uom_id',
    'qty_input',
    'qty_base',
    'price',
    'discount',
    'subtotal',
  ];

  /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

  public function salesOrder()
  {
    return $this->belongsTo(SalesOrder::class);
  }

  public function product()
  {
    return $this->belongsTo(\App\Models\Master\Product::class);
  }

  public function uom()
  {
    return $this->belongsTo(\App\Models\Master\Unit::class, 'uom_id');
  }

  protected $casts = [
    'qty_input' => 'float',
    'qty_base'  => 'float',
    'price'     => 'float',
    'discount'  => 'float',
    'subtotal'  => 'float',
  ];
}
