<?php

namespace App\Listeners\Inventory;

use App\Events\Sales\RefundApproved;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;

class ReverseStockAfterRefundListener
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function handle(RefundApproved $event): void
    {
        $refund = $event->refund;
        $salesOrder = $refund->salesOrder->load('items');

        DB::transaction(function () use ($salesOrder, $refund) {
            foreach ($salesOrder->items as $item) {
                $this->inventoryService->stockIn(
                    $salesOrder->store_id,
                    $item->product_id,
                    $item->qty_base,
                    'refund',
                    $refund->id
                );
            }
        });
    }
}
