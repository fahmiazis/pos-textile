<?php

namespace App\Services\Sales;

use App\Models\Sales\Billing;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class BillingService
{
  /**
   * Create billing from sales order
   */
  public function createFromSalesOrder(int $salesOrderId): Billing
  {
    return DB::transaction(function () use ($salesOrderId) {

      $salesOrder = SalesOrder::with('items')->lockForUpdate()->findOrFail($salesOrderId);

      if ($salesOrder->status !== 'submitted') {
        throw new Exception('Sales order belum disubmit');
      }

      // Cegah double billing
      if ($salesOrder->billings()->exists()) {
        throw new Exception('Billing sudah dibuat untuk sales order ini');
      }

      $billing = Billing::create([
        'billing_number' => $this->generateBillingNumber(),
        'sales_order_id' => $salesOrder->id,
        'billing_date'   => now()->toDateString(),
        'total_amount'  => $salesOrder->total_amount,
        'paid_amount'   => 0,
        'status'        => 'unpaid',
      ]);

      return $billing;
    });
  }

  protected function generateBillingNumber(): string
  {
    return 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
  }
}