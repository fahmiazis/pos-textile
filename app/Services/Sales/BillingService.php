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

      $salesOrder = SalesOrder::lockForUpdate()->findOrFail($salesOrderId);

      if ($salesOrder->status !== 'submitted') {
        throw new Exception('Sales order belum disubmit');
      }

      // Cegah double billing
      if ($salesOrder->billings()->whereNotIn('status', ['cancelled'])->exists()) {
        throw new Exception('Billing aktif sudah ada untuk sales order ini');
      }

      return Billing::create([
        'billing_number' => $this->generateBillingNumber(),

        'sales_order_id' => $salesOrder->id,

        // 'source_document_type' => 'sales_order',
        // 'source_document_id'   => $salesOrder->id,

        'billing_date'   => now()->toDateString(),
        'total_amount'  => $salesOrder->total_amount,
        'paid_amount'   => 0,
        'status'        => 'unpaid',
      ]);
    });
  }

  protected function generateBillingNumber(): string
  {
    return 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
  }

  public function list(array $filters = [])
  {
    $query = Billing::with([
      'salesOrder.customer',
      'collections'
    ]);

    if (!empty($filters['status'])) {
      $statuses = is_array($filters['status'])
        ? $filters['status']
        : [$filters['status']];

      $query->whereIn('status', $statuses);
    }

    return $query->latest()->get();
  }
}
