<?php

namespace App\Services\Sales;

use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class SalesOrderService
{
  protected InventoryService $inventoryService;

  public function __construct(InventoryService $inventoryService)
  {
    $this->inventoryService = $inventoryService;
  }

  /**
   * Create Sales Order (DRAFT)
   */
  public function create(array $data, int $userId): SalesOrder
  {
    return DB::transaction(function () use ($data, $userId) {

      $salesOrder = SalesOrder::create([
        'so_number'   => $this->generateSoNumber(),
        'store_id'    => $data['store_id'],
        'customer_id' => $data['customer_id'],
        'order_date'  => $data['order_date'],
        'status'      => 'draft',
        'created_by'  => $userId,
        'notes'       => $data['notes'] ?? null,
      ]);

      $totalQty = 0;
      $totalAmount = 0;

      foreach ($data['items'] as $item) {
        $subtotal = ($item['price'] * $item['qty_base']) - ($item['discount'] ?? 0);

        SalesOrderItem::create([
          'sales_order_id' => $salesOrder->id,
          'product_id'     => $item['product_id'],
          'uom_id'         => $item['uom_id'],
          'qty_input'      => $item['qty_input'],
          'qty_base'       => $item['qty_base'],
          'price'          => $item['price'],
          'discount'       => $item['discount'] ?? 0,
          'subtotal'       => $subtotal,
        ]);

        $totalQty += $item['qty_base'];
        $totalAmount += $subtotal;
      }

      $salesOrder->update([
        'total_qty'    => $totalQty,
        'total_amount' => $totalAmount,
      ]);

      return $salesOrder;
    });
  }

  /**
   * Submit Sales Order → reserve stock
   */
  public function submit(int $salesOrderId): void
  {
    DB::transaction(function () use ($salesOrderId) {

      $salesOrder = SalesOrder::with('items')->lockForUpdate()->findOrFail($salesOrderId);

      if ($salesOrder->status !== 'draft') {
        throw new Exception('Sales order bukan status draft');
      }

      foreach ($salesOrder->items as $item) {
        $this->inventoryService->reserveStock(
          $salesOrder->store_id,
          $item->product_id,
          $item->qty_base,
          'sales_order',
          $salesOrder->id
        );
      }

      $salesOrder->update([
        'status'       => 'submitted',
        'submitted_at' => now(),
      ]);
    });
  }

  /**
   * Cancel Sales Order → release reserved stock
   */
  public function cancel(int $salesOrderId): void
  {
    DB::transaction(function () use ($salesOrderId) {

      $salesOrder = SalesOrder::with('items')->lockForUpdate()->findOrFail($salesOrderId);

      if (!in_array($salesOrder->status, ['draft', 'submitted'])) {
        throw new Exception('Sales order tidak bisa dibatalkan');
      }

      if ($salesOrder->status === 'submitted') {
        foreach ($salesOrder->items as $item) {
          $this->inventoryService->releaseReservedStock(
            $salesOrder->store_id,
            $item->product_id,
            $item->qty_base,
            'sales_order',
            $salesOrder->id
          );
        }
      }

      $salesOrder->update([
        'status'        => 'cancelled',
        'cancelled_at'  => now(),
      ]);
    });
  }

  /**
   * Generate SO Number
   */
  protected function generateSoNumber(): string
  {
    return 'SO-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
  }
}