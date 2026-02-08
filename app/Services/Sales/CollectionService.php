<?php

namespace App\Services\Sales;

use App\Models\Sales\Billing;
use App\Models\Sales\Collection;
use App\Events\Sales\BillingPaid;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Services\Common\DocumentNumberService;

class CollectionService
{
  public function pay(
    int $billingId,
    float $amount,
    string $paymentMethod,
    ?int $userId = null,
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

      $remainingAmount = (float) $billing->reminder_amount;
      if ($amount > $remainingAmount) {
        throw new Exception('Nominal pembayaran melebihi sisa tagihan');
      }

      $collection = Collection::create([
        'collection_number' => DocumentNumberService::generate(
          'collections',
          'collection_number',
          'COL'
        ),
        'billing_id'     => $billing->id,
        'payment_date'   => now(),
        'amount'         => $amount,
        'payment_method' => $paymentMethod,
        'notes'          => $notes,
        'created_by'     => $userId,
      ]);


      $billing->paid_amount += $amount;
      $billing->reminder_amount -= $amount;

      if ($billing->reminder_amount <= 0) {
        $billing->paid_amount = $billing->total_amount;
        $billing->reminder_amount = 0;
        $billing->status = 'paid';
      } else {
        $billing->status = 'partial';
      }

      $billing->save();

      // dispatch event SETELAH billing 
      if ($billing->status === 'paid') {
        BillingPaid::dispatch($billing);
      }

      return $collection;
    });
  }
}
