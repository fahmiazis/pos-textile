<?php

namespace App\Events\Sales;

use App\Models\Sales\Billing;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BillingPaid
{
  use Dispatchable, SerializesModels;

  public function __construct(
    public Billing $billing
  ) {}
}
