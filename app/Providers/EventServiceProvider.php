<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\Sales\BillingPaid;
use App\Events\Sales\RefundApproved;
use App\Listeners\Sales\HandleBillingPaid;
use App\Listeners\Sales\UpdateBillingAfterRefundListener;
use App\Listeners\Sales\FinalizeRefundListener;

// Inventory listeners
use App\Listeners\Inventory\ReverseStockAfterRefundListener;

class EventServiceProvider extends ServiceProvider
{
  protected $listen = [
    BillingPaid::class => [
      HandleBillingPaid::class,
    ],

    RefundApproved::class => [
      ReverseStockAfterRefundListener::class,
      UpdateBillingAfterRefundListener::class,
      FinalizeRefundListener::class,
    ],
  ];
}
