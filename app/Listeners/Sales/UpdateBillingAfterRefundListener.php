<?php

namespace App\Listeners\Sales;

use App\Events\Sales\RefundApproved;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateBillingAfterRefundListener implements ShouldQueue
{
    public function handle(RefundApproved $event): void
    {
        $billing = $event->refund->billing;

        $billing->update([
            'status'      => 'refunded',
            'paid_amount' => 0,
        ]);
    }
}
