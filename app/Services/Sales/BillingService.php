<?php

namespace App\Services\Sales;

use App\Models\Sales\Billing;
use App\Models\Sales\SalesOrder;
use App\Services\Common\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Exception;

class BillingService
{
  public function createFromSalesOrder(int $salesOrderId): Billing
  {
    return DB::transaction(function () use ($salesOrderId) {

      $salesOrder = SalesOrder::lockForUpdate()->findOrFail($salesOrderId);

      if ($salesOrder->status !== 'submitted') {
        throw new Exception('Sales order belum disubmit');
      }

      if ($salesOrder->billings()->whereNotIn('status', ['cancelled'])->exists()) {
        throw new Exception('Billing aktif sudah ada');
      }

      return Billing::create([
        'invoice_number' => DocumentNumberService::generate(
          'billings',
          'invoice_number',
          'INV'
        ),
        'sales_order_id' => $salesOrder->id,
        'billing_date'   => now()->toDateString(),
        'total_amount'  => $salesOrder->total_amount,
        'paid_amount'   => 0,
        'status'        => 'unpaid',
      ]);
    });
  }

  public function list(array $filters = [])
  {
    $query = Billing::query()
      ->with([
        'salesOrder.customer',
        'collections'
      ]);

    if (!empty($filters['status'])) {
      $statuses = is_array($filters['status'])
        ? $filters['status']
        : [$filters['status']];

      $query->whereIn('status', $statuses);
    }

    return $query
      ->orderByDesc('id')
      ->get();
  }
}
