<?php

namespace App\Services\Sales;

use App\Models\Sales\Billing;
use App\Models\Sales\Collection;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;
use Exception;

class CollectionService
{
  protected InventoryService $inventoryService;

  public function __construct(InventoryService $inventoryService)
  {
    $this->inventoryService = $inventoryService;
  }

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

      $billing = Billing::with('salesOrder.items')->lockForUpdate()->findOrFail($billingId);

      if ($billing->status === 'paid') {
        throw new Exception('Billing sudah lunas');
      }

      // Create collection
      $collection = Collection::create([
        'billing_id'     => $billing->id,
        'payment_date'   => now()->toDateString(),
        'amount'         => $amount,
        'payment_method' => $paymentMethod,
        'notes'          => $notes,
        'created_by'     => $userId,
      ]);

      // Update billing paid amount
      $billing->paid_amount += $amount;

      if ($billing->paid_amount >= $billing->total_amount) {
        $billing->status = 'paid';
      } else {
        $billing->status = 'partial';
      }

      $billing->save();

      // Jika sudah lunas → stock out
      if ($billing->status === 'paid') {
        foreach ($billing->salesOrder->items as $item) {
          $this->inventoryService->stockOut(
            $billing->salesOrder->store_id,
            $item->product_id,
            $item->qty_base,
            'collection',
            $collection->id
          );
        }

        // Tandai sales order selesai
        $billing->salesOrder->update([
          'status'       => 'completed',
          'completed_at' => now(),
        ]);
      }

      return $collection;
    });
  }
}