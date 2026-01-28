<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\Sales\BillingPaid;
use App\Listeners\Sales\HandleBillingPaid;

class EventServiceProvider extends ServiceProvider
{
  protected $listen = [
    BillingPaid::class => [
      HandleBillingPaid::class,
    ],
  ];
}
