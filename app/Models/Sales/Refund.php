<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
  protected $fillable = [
    'refund_number',
    'sales_order_id',
    'billing_id',
    'refund_number',
    'amount',
    'status',
    'reason',
  ];

  public function salesOrder()
  {
    return $this->belongsTo(SalesOrder::class);
  }

  public function billing()
  {
    return $this->belongsTo(Billing::class);
  }
}