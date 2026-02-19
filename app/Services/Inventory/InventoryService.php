<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
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
