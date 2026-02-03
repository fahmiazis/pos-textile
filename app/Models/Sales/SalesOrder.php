<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesOrder extends Model
{
  use HasFactory;

  protected $table = 'sales_orders';

  protected $fillable = [
    'so_number',
    'store_id',
    'customer_id',
    'status',
    'order_date',
    'submitted_at',
    'completed_at',
    'cancelled_at',
    'total_qty',
    'total_amount',
    'created_by',
    'notes',
    // 'source_document_type',
    // 'source_document_id',
  ];

  /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

  public function items()
  {
    return $this->hasMany(SalesOrderItem::class);
  }

  public function store()
  {
    return $this->belongsTo(\App\Models\Master\Store::class);
  }

  public function customer()
  {
    return $this->belongsTo(\App\Models\Master\Customer::class);
  }

  public function billings()
  {
    return $this->hasMany(Billing::class);
  }
  protected $casts = [
    'total_qty'    => 'float',
    'total_amount' => 'float',
  ];
}
