<?php

namespace App\Services\Sales;

use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\Master\Unit;
use App\Services\Common\DocumentNumberService;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;
use Exception;

class SalesOrderService
{
  protected InventoryService $inventoryService;

  public function __construct(InventoryService $inventoryService)
  {
    $this->inventoryService = $inventoryService;
  }

  /**
   * =========================
   * CREATE SALES ORDER (DRAFT)
   * =========================
   */
  public function create(array $data, ?int $userId): SalesOrder
  {
    if (!$userId) {
      throw new Exception('User not authenticated');
    }

    return DB::transaction(function () use ($data, $userId) {

      $order = SalesOrder::create([
        'so_number'   => DocumentNumberService::generate(
          'sales_orders',
          'so_number',
          'SO'
        ),
        'store_id'    => $data['store_id'],
        'customer_id' => $data['customer_id'],
        'order_date'  => $data['order_date'],
        'status'      => 'draft',
        'created_by'  => $userId,
        'notes'       => $data['notes'] ?? null,
      ]);

      $this->syncItems($order, $data['items']);

      return $order;
    });
  }

  /**
   * =========================
   * UPDATE SALES ORDER (DRAFT)
   * =========================
   */
  public function updateDraft(int $id, array $data): SalesOrder
  {
    return DB::transaction(function () use ($id, $data) {

      $order = SalesOrder::with('items')
        ->lockForUpdate()
        ->findOrFail($id);

      if ($order->status !== 'draft') {
        throw new Exception('Sales order tidak bisa diedit karena sudah disubmit');
      }

      // update header
      $order->update([
        'customer_id' => $data['customer_id'],
        'order_date'  => $data['order_date'],
        'notes'       => $data['notes'] ?? $order->notes,
      ]);

      // reset items
      $order->items()->delete();

      $this->syncItems($order, $data['items']);

      return $order;
    });
  }

  /**
   * =========================
   * SUBMIT SALES ORDER
   * (RESERVE STOCK)
   * =========================
   */
  public function submit(int $salesOrderId): SalesOrder
  {
    return DB::transaction(function () use ($salesOrderId) {

      $order = SalesOrder::with('items')
        ->lockForUpdate()
        ->findOrFail($salesOrderId);

      if ($order->status !== 'draft') {
        throw new Exception('Sales order tidak bisa disubmit');
      }

      foreach ($order->items as $item) {
        $this->inventoryService->reserveStock(
          $order->store_id,
          $item->product_id,
          $item->qty_base,
          'sales_order',
          $order->id
        );
      }

      $order->update([
        'status'       => 'submitted',
        'submitted_at' => now(),
      ]);

      return $order;
    });
  }

  /**
   * =========================
   * CANCEL SALES ORDER
   * (RELEASE RESERVED STOCK)
   * =========================
   */
  public function cancel(int $salesOrderId): SalesOrder
  {
    return DB::transaction(function () use ($salesOrderId) {

      $order = SalesOrder::with('items')
        ->lockForUpdate()
        ->findOrFail($salesOrderId);

      if (!in_array($order->status, ['draft', 'submitted'])) {
        throw new Exception('Sales order tidak bisa dibatalkan');
      }

      if ($order->status === 'submitted') {
        foreach ($order->items as $item) {
          $this->inventoryService->releaseReservedStock(
            $order->store_id,
            $item->product_id,
            $item->qty_base,
            'sales_order',
            $order->id
          );
        }
      }

      $order->update([
        'status'       => 'cancelled',
        'cancelled_at' => now(),
      ]);

      return $order;
    });
  }

  /**
   * ==================================================
   * INTERNAL HELPER
   * HITUNG QTY BASE, SUBTOTAL, TOTAL (SINGLE SOURCE)
   * ==================================================
   */
  private function syncItems(SalesOrder $order, array $items): void
  {
    $totalQty = 0;
    $totalAmount = 0;

    foreach ($items as $item) {

      $uom = Unit::findOrFail($item['uom_id']);

      // 🔒 SINGLE SOURCE OF TRUTH
      $qtyBase = $item['qty_input'] * $uom->multiplier;

      $subtotal = ($item['price'] * $qtyBase)
        - ($item['discount'] ?? 0);

      SalesOrderItem::create([
        'sales_order_id' => $order->id,
        'product_id'     => $item['product_id'],
        'uom_id'         => $uom->id,
        'qty_input'      => $item['qty_input'],
        'qty_base'       => $qtyBase,
        'price'          => $item['price'],
        'discount'       => $item['discount'] ?? 0,
        'subtotal'       => $subtotal,
      ]);

      $totalQty += $qtyBase;
      $totalAmount += $subtotal;
    }

    $order->update([
      'total_qty'    => $totalQty,
      'total_amount' => $totalAmount,
    ]);
  }
}