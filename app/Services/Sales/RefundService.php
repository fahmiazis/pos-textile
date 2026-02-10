<?php

namespace App\Services\Sales;

use App\Models\Sales\Refund;
use App\Models\Sales\SalesOrder;
use App\Events\Sales\RefundApproved;
use App\Services\Common\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Exception;

class RefundService
{
  public function fullRefund(SalesOrder $salesOrder, ?string $reason = null): Refund
  {
    return DB::transaction(function () use ($salesOrder, $reason) {

      $existingRefund = Refund::where('sales_order_id', $salesOrder->id)
        ->latest()
        ->first();

      if ($existingRefund) {
        throw new Exception('Sales order sudah direfund');
      }

      $billing = $salesOrder->billings()
        ->where('status', 'paid')
        ->lockForUpdate()
        ->latest()
        ->first();

      if (! $billing) {
        throw new Exception('Sales order belum lunas');
      }

      if ($billing->status === 'refunded') {
        throw new Exception('Billing sudah direfund');
      }

      $refund = Refund::create([
        'refund_number' => DocumentNumberService::generate(
          'refunds',
          'refund_number',
          'RF'
        ),
        'sales_order_id' => $salesOrder->id,
        'billing_id'     => $billing->id,
        'amount'         => $billing->paid_amount,
        'status'         => 'approved',
        'reason'         => $reason,
      ]);

      $billing->update([
        'status'      => 'refunded',
        'paid_amount' => 0,
      ]);

      if ($salesOrder->status !== 'cancelled') {
        $salesOrder->update(['status' => 'cancelled']);
      }

      RefundApproved::dispatch($refund);

      return $refund;
    });
  }
}
