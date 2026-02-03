<?php

namespace App\Events\Sales;

use App\Models\Sales\Refund;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefundApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Refund $refund
    ) {}
}
