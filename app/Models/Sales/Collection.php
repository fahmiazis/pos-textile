<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Collection extends Model
{
  use HasFactory;

  protected $table = 'collections';

  protected $fillable = [
    'billing_id',
    'payment_date',
    'amount',
    'payment_method',
    'notes',
    'created_by',
  ];

  public function billing()
  {
    return $this->belongsTo(Billing::class);
  }
}
