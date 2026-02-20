<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Inventory\InventoryService;
use App\Models\Inventory\Inventory;
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
    return response()->json([
      'data' => $this->inventoryService->getAvailability($request->only([
        'store_id',
        'product_id',
        'available_only',
      ]))
    ]);
  }

  /**
   * GET /inventory/movements
   * Inventory ledger / audit
   */
  public function movements(Request $request)
  {
    $movements = $this->inventoryService->getMovements($request->only([
      'store_id',
      'product_id',
      'reference_type',
      'reference_id',
      'with_balance',
    ]));

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
