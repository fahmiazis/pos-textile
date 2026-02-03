<?php

namespace App\Listeners\Sales;

use App\Events\Sales\RefundApproved;
use Illuminate\Contracts\Queue\ShouldQueue;

class FinalizeRefundListener implements ShouldQueue
{
    public function handle(RefundApproved $event): void
    {
        $event->refund->salesOrder->update([
            'status' => 'refunded',
        ]);

        $event->refund->update([
            'status' => 'processed',
        ]);
    }
}
