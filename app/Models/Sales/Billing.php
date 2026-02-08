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
    'subtotal_amount',
    'tax_rate',
    'tax_amount',
    'total_amount',
    'paid_amount',
    'reminder_amount',
    'status',
  ];

  protected $casts = [
    'subtotal_amount' => 'float',
    'tax_rate' => 'float',
    'tax_amount' => 'float',
    'total_amount' => 'float',
    'paid_amount' => 'float',
    'reminder_amount' => 'float',
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
