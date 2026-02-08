<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Billing extends Model
{
  use HasFactory;

  protected $table = 'billings';

  protected $fillable = [
    'invoice_number',
    'sales_order_id',
    'billing_date',
    'total_amount',
    'paid_amount',
    'reminder_amount',
    'status',
  ];

  public function salesOrder()
  {
    return $this->belongsTo(SalesOrder::class);
  }

  public function collections()
  {
    return $this->hasMany(Collection::class);
  }
}
