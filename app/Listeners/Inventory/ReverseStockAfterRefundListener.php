<?php

namespace App\Listeners\Inventory;

use App\Events\Sales\RefundApproved;
use App\Services\Inventory\InventoryService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReverseStockAfterRefundListener implements ShouldQueue
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function handle(RefundApproved $event): void
    {
        $salesOrder = $event->refund->salesOrder;

        foreach ($salesOrder->items as $item) {
            $this->inventoryService->stockIn(
                productId: $item->product_id,
                qty: $item->qty,
                reference: 'REFUND-' . $salesOrder->order_number
            );
        }
    }
}
