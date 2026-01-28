<?php

namespace App\Services\Sales;

use App\Models\Sales\Billing;
use App\Models\Sales\Collection;
use App\Events\Sales\BillingPaid;
use Illuminate\Support\Facades\DB;
use Exception;

class CollectionService
{
  /**
   * Create collection (payment)
   */
  public function pay(
    int $billingId,
    float $amount,
    string $paymentMethod,
    int $userId,
    ?string $notes = null
  ): Collection {

    return DB::transaction(function () use (
      $billingId,
      $amount,
      $paymentMethod,
      $userId,
      $notes
    ) {

      $billing = Billing::lockForUpdate()->findOrFail($billingId);

      if ($billing->status === 'paid') {
        throw new Exception('Billing sudah lunas');
      }

      if (($billing->paid_amount + $amount) > $billing->total_amount) {
        throw new Exception('Pembayaran melebihi total billing');
      }

      // Simpan collection
      $collection = Collection::create([
        'billing_id'     => $billing->id,
        'payment_date'   => now()->toDateString(),
        'amount'         => $amount,
        'payment_method' => $paymentMethod,
        'notes'          => $notes,
        'created_by'     => $userId,
      ]);

      // Update billing
      $billing->paid_amount += $amount;

      if ($billing->paid_amount >= $billing->total_amount) {

        $billing->update([
          'paid_amount' => $billing->paid_amount,
          'status'      => 'paid',
        ]);

        //  FIRE EVENT
        BillingPaid::dispatch($billing);
      } else {

        $billing->update([
          'paid_amount' => $billing->paid_amount,
          'status'      => 'partial',
        ]);
      }

      return $collection;
    });
  }
}
