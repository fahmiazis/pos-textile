<?php

namespace App\Services\Sales;

use App\Models\Sales\Refund;
use App\Models\Sales\SalesOrder;
use App\Events\Sales\RefundApproved;
use Illuminate\Support\Facades\DB;
use Exception;

class RefundService
{
  public function fullRefund(SalesOrder $salesOrder, ?string $reason = null): Refund
  {
    return DB::transaction(function () use ($salesOrder, $reason) {

      // Ambil billing yang PAID
      $billing = $salesOrder->billings()
        ->where('status', 'paid')
        ->latest()
        ->lockForUpdate()
        ->first();

      if (! $billing) {
        throw new Exception('Sales order belum lunas / billing tidak valid');
      }

      // Cegah double refund
      if ($billing->status === 'refunded') {
        throw new Exception('Billing sudah direfund');
      }

      // Create refund
      $refund = Refund::create([
        'sales_order_id' => $salesOrder->id,
        'billing_id'     => $billing->id,
        'amount'         => $billing->paid_amount,
        'status'         => 'approved',
        'reason'         => $reason,
      ]);

      // Update billing (INI YANG PENTING)
      $billing->update([
        'status'      => 'refunded',
        'paid_amount' => 0,
      ]);

      // OPTIONAL tapi sehat secara bisnis
      if ($salesOrder->status !== 'cancelled') {
        $salesOrder->update([
          'status' => 'cancelled',
        ]);
      }

      event(new RefundApproved($refund));

      return $refund;
    });
  }
}
