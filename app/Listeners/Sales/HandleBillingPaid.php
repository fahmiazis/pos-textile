<?php

namespace App\Listeners\Sales;

use App\Events\Sales\BillingPaid;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;

class HandleBillingPaid
{
  protected InventoryService $inventoryService;

  public function __construct(InventoryService $inventoryService)
  {
    $this->inventoryService = $inventoryService;
  }

  public function handle(BillingPaid $event): void
  {
    $billing = $event->billing->load('salesOrder.items');

    DB::transaction(function () use ($billing) {

      foreach ($billing->salesOrder->items as $item) {
        $this->inventoryService->stockOut(
          $billing->salesOrder->store_id,
          $item->product_id,
          $item->qty_base,
          'billing',
          $billing->id
        );
      }

      $billing->salesOrder->update([
        'status'       => 'completed',
        'completed_at' => now(),
      ]);
    });
  }
}
