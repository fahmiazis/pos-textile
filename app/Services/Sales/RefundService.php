<?php

namespace App\Services\Sales;

use App\Models\Sales\Refund;
use App\Models\Sales\SalesOrder;
use App\Events\Sales\RefundApproved;
use Illuminate\Support\Facades\DB;

class RefundService
{
  public function fullRefund(SalesOrder $salesOrder, ?string $reason = null): Refund
  {
    return DB::transaction(function () use ($salesOrder, $reason) {

      $billing = $salesOrder->billing;

      if ($billing->status !== 'paid') {
        throw new \Exception('Only PAID billing can be refunded');
      }

      $refund = Refund::create([
        'sales_order_id' => $salesOrder->id,
        'billing_id'     => $billing->id,
        'amount'         => $billing->paid_amount,
        'status'         => 'approved',
        'reason'         => $reason,
      ]);

      event(new RefundApproved($refund));

      return $refund;
    });
  }
}
