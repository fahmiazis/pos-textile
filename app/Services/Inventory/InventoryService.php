<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryMovement;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Sales\Billing;
use App\Models\Sales\Refund;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
  /**
   * Inventory movements (ledger) with optional filters.
   */
  public function getMovements(array $filters)
  {
    $query = InventoryMovement::query()
      ->with([
        'inventory.store:id,code,name',
        'inventory.product:id,sku,name,base_uom_id',
        'inventory.product.baseUom:id,code,name'
      ]);

    if (!empty($filters['store_id'])) {
      $query->whereHas(
        'inventory',
        fn($q) =>
        $q->where('store_id', $filters['store_id'])
      );
    }

    if (!empty($filters['product_id'])) {
      $query->whereHas(
        'inventory',
        fn($q) =>
        $q->where('product_id', $filters['product_id'])
      );
    }

    if (!empty($filters['reference_type'])) {
      $query->where('reference_type', $filters['reference_type']);
    }

    if (!empty($filters['reference_id'])) {
      $query->where('reference_id', $filters['reference_id']);
    }

    $movements = $query
      ->orderByDesc('id')
      ->limit(200)
      ->get();

    $referenceIdsByType = $movements
      ->groupBy('reference_type')
      ->map(fn($items) => $items->pluck('reference_id')->filter()->unique()->values());

    $referenceNumbersByType = [
      'sales_order' => SalesOrder::whereIn('id', $referenceIdsByType->get('sales_order', []))
        ->pluck('so_number', 'id'),
      'billing' => Billing::whereIn('id', $referenceIdsByType->get('billing', []))
        ->pluck('invoice_number', 'id'),
      'purchase_order' => PurchaseOrder::whereIn('id', $referenceIdsByType->get('purchase_order', []))
        ->pluck('po_number', 'id'),
      'refund' => Refund::whereIn('id', $referenceIdsByType->get('refund', []))
        ->pluck('refund_number', 'id'),
    ];

    $movements->transform(function ($movement) use ($referenceNumbersByType) {
      $type = $movement->reference_type;
      $id = $movement->reference_id;

      $movement->reference_number = $referenceNumbersByType[$type]->get($id) ?? null;
      $uom = $movement->inventory?->product?->baseUom;
      $movement->uom = $uom
        ? [
          'id' => $uom->id,
          'code' => $uom->code,
          'name' => $uom->name,
        ]
        : null;

      return $movement;
    });

    return $movements;
  }

  /**
   * Inventory availability with optional filters.
   */
  public function getAvailability(array $filters)
  {
    $query = Inventory::query()
      ->with([
        'store:id,code,name',
        'product:id,sku,name,base_uom_id',
        'product.baseUom:id,code,name'
      ]);

    if (!empty($filters['store_id'])) {
      $query->where('store_id', $filters['store_id']);
    }

    if (!empty($filters['product_id'])) {
      $query->where('product_id', $filters['product_id']);
    }

    if (!empty($filters['available_only'])) {
      $query->where('stock_available', '>', 0);
    }

    return $query->orderBy('id')->get();
  }

  /**
   * Pastikan inventory row ada
   */
  public function initInventory(int $storeId, int $productId): Inventory
  {
    return Inventory::firstOrCreate(
      [
        'store_id' => $storeId,
        'product_id' => $productId,
      ],
      [
        'stock_on_hand' => 0,
        'stock_reserved' => 0,
        'stock_available' => 0,
      ]
    );
  }

  /**
   * Reserve stock saat Sales Order submit
   */
  public function reserveStock(
    int $storeId,
    int $productId,
    float $qty,
    string $referenceType,
    int $referenceId
  ): void {
    DB::transaction(function () use (
      $storeId,
      $productId,
      $qty,
      $referenceType,
      $referenceId
    ) {
      $inventory = $this->initInventory($storeId, $productId);

      if ($inventory->stock_available < $qty) {
        throw new Exception('Stock available tidak mencukupi');
      }

      // update inventory snapshot
      $stockBefore = $inventory->stock_on_hand;
      $inventory->stock_reserved += $qty;
      $inventory->stock_available -= $qty;
      $inventory->save();

      // log movement
      InventoryMovement::create([
        'inventory_id' => $inventory->id,
        'type' => 'reserve',
        'qty' => $qty,
        'stock_before' => $stockBefore,
        'stock_after' => $stockBefore,
        'reference_type' => $referenceType,
        'reference_id' => $referenceId,
        'notes' => 'Reserve stock from sales order',
      ]);
    });
  }

  /**
   * Release reserved stock (SO dibatalkan)
   */
  public function releaseReservedStock(
    int $storeId,
    int $productId,
    float $qty,
    string $referenceType,
    int $referenceId
  ): void {
    DB::transaction(function () use (
      $storeId,
      $productId,
      $qty,
      $referenceType,
      $referenceId
    ) {
      $inventory = $this->initInventory($storeId, $productId);

      if ($inventory->stock_reserved < $qty) {
        throw new Exception('Reserved stock tidak mencukupi');
      }

      $stockBefore = $inventory->stock_on_hand;
      $inventory->stock_reserved -= $qty;
      $inventory->stock_available += $qty;
      $inventory->save();

      InventoryMovement::create([
        'inventory_id' => $inventory->id,
        'type' => 'release',
        'qty' => $qty,
        'stock_before' => $stockBefore,
        'stock_after' => $stockBefore,
        'reference_type' => $referenceType,
        'reference_id' => $referenceId,
        'notes' => 'Release reserved stock',
      ]);
    });
  }

  /**
   * Final stock out (collection paid)
   */
  public function stockOut(
    int $storeId,
    int $productId,
    float $qty,
    string $referenceType,
    int $referenceId
  ): void {
    DB::transaction(function () use (
      $storeId,
      $productId,
      $qty,
      $referenceType,
      $referenceId
    ) {
      $inventory = $this->initInventory($storeId, $productId);

      if ($inventory->stock_reserved < $qty) {
        throw new Exception('Reserved stock tidak cukup untuk stock out');
      }

      $stockBefore = $inventory->stock_on_hand;
      $inventory->stock_reserved -= $qty;
      $inventory->stock_on_hand -= $qty;
      $inventory->save();

      InventoryMovement::create([
        'inventory_id' => $inventory->id,
        'type' => 'out',
        'qty' => $qty,
        'stock_before' => $stockBefore,
        'stock_after' => $inventory->stock_on_hand,
        'reference_type' => $referenceType,
        'reference_id' => $referenceId,
        'notes' => 'Stock out after payment',
      ]);
    });
  }

  /**
   * Stock in (purchase / GR)
   */
  public function stockIn(
    int $storeId,
    int $productId,
    float $qty,
    string $referenceType,
    int $referenceId
  ): void {
    DB::transaction(function () use (
      $storeId,
      $productId,
      $qty,
      $referenceType,
      $referenceId
    ) {
      $inventory = $this->initInventory($storeId, $productId);

      $stockBefore = $inventory->stock_on_hand;
      $inventory->stock_on_hand += $qty;
      $inventory->stock_available += $qty;
      $inventory->save();

      InventoryMovement::create([
        'inventory_id' => $inventory->id,
        'type' => 'in',
        'qty' => $qty,
        'stock_before' => $stockBefore,
        'stock_after' => $inventory->stock_on_hand,
        'reference_type' => $referenceType,
        'reference_id' => $referenceId,
        'notes' => 'Stock in',
      ]);
    });
  }
}
