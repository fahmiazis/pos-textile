<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Inventory\InventoryService;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryMovement;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\Billing;
use App\Models\Sales\Refund;
use App\Models\Purchase\PurchaseOrder;
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

      return $movement;
    });

    return response()->json([
      'data' => $movements
    ]);
  }

  /**
   * Tambah stock manual (initial stock / gudang)
   */
  public function stockIn(Request $request)
  {
    $data = $request->validate([
      'store_id'   => 'required|exists:stores,id',
      'product_id' => 'required|exists:products,id',
      'qty'        => 'required|numeric|min:0.01',
      'notes'      => 'nullable|string',
    ]);

    $this->inventoryService->stockIn(
      $data['store_id'],
      $data['product_id'],
      $data['qty'],
      'manual',
      0
    );

    return response()->json([
      'success' => true,
      'message' => 'Stock berhasil ditambahkan',
    ]);
  }
}
