<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Inventory\InventoryService;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryMovement;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
  protected InventoryService $inventoryService;

  public function __construct(InventoryService $inventoryService)
  {
    $this->inventoryService = $inventoryService;
  }

  /**
   * GET /inventory/availability
   * Snapshot stock
   */
  public function availability(Request $request)
  {
    $query = Inventory::query()
      ->with([
        'store:id,code,name',
        'product:id,sku,name,base_uom_id',
        'product.baseUom:id,code,name'
      ]);

    if ($request->filled('store_id')) {
      $query->where('store_id', $request->store_id);
    }

    if ($request->filled('product_id')) {
      $query->where('product_id', $request->product_id);
    }

    if ($request->boolean('available_only')) {
      $query->where('stock_available', '>', 0);
    }

    return response()->json([
      'data' => $query->orderBy('id')->get()
    ]);
  }

  /**
   * GET /inventory/movements
   * Inventory ledger / audit
   */
  public function movements(Request $request)
  {
    $query = InventoryMovement::query()
      ->with([
        'inventory.store:id,code,name',
        'inventory.product:id,sku,name'
      ]);

    if ($request->filled('store_id')) {
      $query->whereHas(
        'inventory',
        fn($q) =>
        $q->where('store_id', $request->store_id)
      );
    }

    if ($request->filled('product_id')) {
      $query->whereHas(
        'inventory',
        fn($q) =>
        $q->where('product_id', $request->product_id)
      );
    }

    if ($request->filled('reference_type')) {
      $query->where('reference_type', $request->reference_type);
    }

    if ($request->filled('reference_id')) {
      $query->where('reference_id', $request->reference_id);
    }

    return response()->json([
      'data' => $query
        ->orderByDesc('id')
        ->limit(200)
        ->get()
    ]);
  }
}