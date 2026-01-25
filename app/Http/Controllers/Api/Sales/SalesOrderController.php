<?php

namespace App\Http\Controllers\Api\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\SalesOrderService;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
  protected SalesOrderService $service;

  public function __construct(SalesOrderService $service)
  {
    $this->service = $service;
  }

  /**
   * Create Sales Order (DRAFT)
   */
  public function store(Request $request)
  {
    $data = $request->validate([
      'store_id' => 'required|exists:stores,id',
      'customer_id' => 'required|exists:customers,id',
      'order_date' => 'required|date',
      'notes' => 'nullable|string',

      'items' => 'required|array|min:1',
      'items.*.product_id' => 'required|exists:products,id',
      'items.*.uom_id' => 'required|exists:units,id',
      'items.*.qty_input' => 'required|numeric|min:0.001',
      'items.*.qty_base' => 'required|numeric|min:0.001',
      'items.*.price' => 'required|numeric|min:0',
      'items.*.discount' => 'nullable|numeric|min:0',
    ]);

    $salesOrder = $this->service->create($data, auth()->id());

    return response()->json([
      'message' => 'Sales order draft created',
      'data' => $salesOrder->load('items')
    ], 201);
  }

  /**
   * Submit Sales Order (RESERVE STOCK)
   */
  public function submit(int $id)
  {
    $this->service->submit($id);

    return response()->json([
      'message' => 'Sales order submitted and stock reserved'
    ]);
  }

  /**
   * Cancel Sales Order
   */
  public function cancel(int $id)
  {
    $this->service->cancel($id);

    return response()->json([
      'message' => 'Sales order cancelled'
    ]);
  }
}